
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

$baglanti = $db;

$method = $_SERVER["REQUEST_METHOD"];
$businessParam = trim($_GET["isletme"] ?? "");
// Kategorileri listele
if ($method === "GET") {

    if ($businessParam === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "İşletme belirtilmedi."
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
    "SELECT name FROM categories WHERE business_id = ? ORDER BY id ASC"
);

$stmt->bind_param("i", $businessId);
$stmt->execute();

$sonuc = $stmt->get_result();

    $categories = [];

    while ($satir = $sonuc->fetch_assoc()) {
        $categories[] = $satir["name"];
    }

    echo json_encode([
        "success" => true,
        "categories" => $categories
    ], JSON_UNESCAPED_UNICODE);

    $baglanti->close();
    exit;
}

// Yeni kategori ekle
if ($method === "POST") {
$businessId = getBusinessId();
    $veri = json_decode(file_get_contents("php://input"), true);
    $name = trim($veri["name"] ?? "");

    if ($name === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Kategori adı boş olamaz."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $baglanti->prepare(
    "INSERT INTO categories (business_id, name) VALUES (?, ?)"
);

$stmt->bind_param("is", $businessId, $name);

    try {
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Kategori eklendi."
    ], JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Bu kategori zaten mevcut veya eklenemedi."
    ], JSON_UNESCAPED_UNICODE);
}

    $stmt->close();
    $baglanti->close();
    exit;
}

// Kategori sil
if ($method === "DELETE") {
$businessId = getBusinessId();
    $name = trim($_GET["name"] ?? "");

    if ($name === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Kategori adı belirtilmedi."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $baglanti->prepare(
    "DELETE FROM categories WHERE name = ? AND business_id = ?"
);

$stmt->bind_param("si", $name, $businessId);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Kategori silindi."
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Kategori silinemedi."
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
