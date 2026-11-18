
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

// Yalnızca Developer erişebilir
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Bu işlem için yetkiniz yok."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$veri = json_decode(file_get_contents("php://input"), true);
$businessId = (int)($veri["id"] ?? 0);

if ($businessId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz işletme numarası."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// İşletmenin gerçekten var olduğunu kontrol et
$stmt = $db->prepare(
    "SELECT id FROM businesses WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $businessId);
$stmt->execute();

$result = $stmt->get_result();
$business = $result->fetch_assoc();

$stmt->close();

if (!$business) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "İşletme bulunamadı."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Seçilen işletmeyi Developer oturumuna kaydet
$_SESSION["selected_business_id"] = $businessId;

echo json_encode([
    "success" => true,
    "message" => "İşletme seçildi."
], JSON_UNESCAPED_UNICODE);

?>
