
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

$baglanti = $db;

$method = $_SERVER["REQUEST_METHOD"];


// İşletme bilgilerini getir
if ($method === "GET") {
$businessParam = trim($_GET["isletme"] ?? "");

if ($businessParam === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "İşletme bilgisi belirtilmedi."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (ctype_digit($businessParam)) {
    $stmt = $baglanti->prepare(
        "SELECT id FROM businesses WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param("i", $businessParam);
} else {
    $stmt = $baglanti->prepare(
        "SELECT id FROM businesses WHERE slug = ? LIMIT 1"
    );
    $stmt->bind_param("s", $businessParam);
}
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

$businessId = (int)$business["id"];
    $stmt = $baglanti->prepare(
    "SELECT name, logo FROM restaurant_settings WHERE business_id = ?"
);

$stmt->bind_param("i", $businessId);
$stmt->execute();

$sonuc = $stmt->get_result();

    $restaurant = $sonuc->fetch_assoc();

    if (!$restaurant) {
    $stmt = $baglanti->prepare(
        "SELECT name FROM businesses WHERE id = ?"
    );

    $stmt->bind_param("i", $businessId);
    $stmt->execute();

    $sonuc = $stmt->get_result();
    $business = $sonuc->fetch_assoc();
    $stmt->close();

    $restaurant = [
        "name" => $business["name"] ?? "İşletme",
        "logo" => ""
    ];
}

    echo json_encode([
        "success" => true,
        "restaurant" => $restaurant
    ], JSON_UNESCAPED_UNICODE);

    $baglanti->close();
    exit;
}

// İşletme bilgilerini kaydet
if ($method === "POST") {
$businessId = getBusinessId();
    $veri = json_decode(file_get_contents("php://input"), true);

    $name = trim($veri["name"] ?? "");
    $logo = $veri["logo"] ?? "";

    if ($name === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "İşletme adı boş olamaz."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $baglanti->prepare(
    "INSERT INTO restaurant_settings (id, business_id, name, logo)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        logo = VALUES(logo)"
);

$stmt->bind_param("iiss", $businessId, $businessId, $name, $logo);

    

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "İşletme bilgileri kaydedildi."
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "İşletme bilgileri kaydedilemedi."
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $baglanti->close();
    exit;
}

http_response_code(405);

echo json_encode([
    "success" => false,
    "message" => "Desteklenmeyen işlem."
], JSON_UNESCAPED_UNICODE);

$baglanti->close();

?>
