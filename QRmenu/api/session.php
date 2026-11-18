
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Oturum açmanız gerekiyor."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Developer seçtiği işletmeyi yönetir.
// Normal kullanıcı kendi işletmesini yönetir.
if (
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "admin"
) {
    $businessId = (int)($_SESSION["selected_business_id"] ?? 0);
} else {
    $businessId = (int)($_SESSION["business_id"] ?? 0);
}

if ($businessId <= 0) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Yönetilecek işletme seçilmedi."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $db->prepare(
    "SELECT name, slug FROM businesses WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $businessId);
$stmt->execute();

$sonuc = $stmt->get_result();
$business = $sonuc->fetch_assoc();

$stmt->close();

if (!$business) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "İşletme bulunamadı."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "success" => true,
    "business" => $business,
    "role" => $_SESSION["role"]
], JSON_UNESCAPED_UNICODE);

?>
