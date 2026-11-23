
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

$baglanti = $db;

$method = $_SERVER["REQUEST_METHOD"];


// Ürünleri listele
if ($method === "GET") {

   $businessParam = trim($_GET["isletme"] ?? "");

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
        "SELECT id, name, category, price, description, image, active
         FROM products
         WHERE business_id = ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("i", $businessId);
    $stmt->execute();

    $sonuc = $stmt->get_result();
    $urunler = [];

    while ($satir = $sonuc->fetch_assoc()) {
        $satir["id"] = (int)$satir["id"];
        $satir["price"] = (float)$satir["price"];
        $satir["active"] = (bool)$satir["active"];
        $urunler[] = $satir;
    }

    echo json_encode([
        "success" => true,
        "products" => $urunler
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
    exit;
}

// Yeni ürün ekle
if ($method === "POST") {
$businessId = getBusinessId();
    $veri = json_decode(file_get_contents("php://input"), true);

    if (!is_array($veri)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Geçersiz veri."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $ad = trim($veri["name"] ?? "");
    $kategori = trim($veri["category"] ?? "");
    $fiyat = (float)($veri["price"] ?? 0);
    $aciklama = trim($veri["description"] ?? "");
    $resim = $veri["image"] ?? "";
    $aktif = !empty($veri["active"]) ? 1 : 0;

    if ($ad === "" || $kategori === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Ürün adı ve kategori zorunludur."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "INSERT INTO products
        (business_id, name, category, price, description, image, active)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    

    $stmt = $baglanti->prepare($sql);
$stmt->bind_param(
    "issdssi",
    $businessId,
    $ad,
    $kategori,
    $fiyat,
    $aciklama,
    $resim,
    $aktif
);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Ürün eklendi.",
            "id" => $stmt->insert_id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Ürün eklenemedi."
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $baglanti->close();
    exit;
}

// Mevcut ürünü güncelle
if ($method === "PUT") {
$businessId = getBusinessId();
    $veri = json_decode(file_get_contents("php://input"), true);
if (!is_array($veri)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz veri."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
    $id = (int)($veri["id"] ?? 0);
    $ad = trim($veri["name"] ?? "");
    $kategori = trim($veri["category"] ?? "");
    $fiyat = (float)($veri["price"] ?? 0);
    $aciklama = trim($veri["description"] ?? "");
    $resim = $veri["image"] ?? "";
    $aktif = !empty($veri["active"]) ? 1 : 0;

    if ($id <= 0 || $ad === "" || $kategori === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Geçersiz ürün bilgileri."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "UPDATE products
        SET name=?, category=?, price=?, description=?, image=?, active=?
        WHERE id=? AND business_id=?";

    $stmt = $baglanti->prepare($sql);
    $stmt->bind_param(
    "ssdssiii",
    $ad,
    $kategori,
    $fiyat,
    $aciklama,
    $resim,
    $aktif,
    $id,
    $businessId
);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Ürün güncellendi."
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Ürün güncellenemedi."
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $baglanti->close();
    exit;
}


// Ürün sil
if ($method === "DELETE") {
$businessId = getBusinessId();
    $id = (int)($_GET["id"] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Geçersiz ürün numarası."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $baglanti->prepare(
    "DELETE FROM products WHERE id = ? AND business_id = ?"
);
    $stmt->bind_param("ii", $id, $businessId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Ürün silindi."
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Ürün silinemedi."
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $baglanti->close();
    exit;
}

