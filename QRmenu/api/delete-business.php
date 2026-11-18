
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

// Yalnızca kurulum yöneticisi erişebilir
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

// Yalnızca POST isteği kabul edilir
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Desteklenmeyen işlem."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Silinecek işletme ID'sini al
$veri = json_decode(file_get_contents("php://input"), true);
$businessId = (int)($veri["id"] ?? 0);

if ($businessId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz işletme ID'si."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db->begin_transaction();

    // İşletmenin varlığını kontrol et
    $stmt = $db->prepare(
        "SELECT id FROM businesses WHERE id = ? FOR UPDATE"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();

    $business = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$business) {
        throw new Exception("İşletme bulunamadı.");
    }

    // İşletmeye bağlı ürünleri sil
    $stmt = $db->prepare(
        "DELETE FROM products WHERE business_id = ?"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    // İşletmeye bağlı kategorileri sil
    $stmt = $db->prepare(
        "DELETE FROM categories WHERE business_id = ?"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    // İşletme ayarlarını sil
    $stmt = $db->prepare(
        "DELETE FROM restaurant_settings WHERE business_id = ?"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    // İşletmeye bağlı kullanıcıları sil
    $stmt = $db->prepare(
        "DELETE FROM users WHERE business_id = ?"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    // Son olarak işletmeyi sil
    $stmt = $db->prepare(
        "DELETE FROM businesses WHERE id = ?"
    );
    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "İşletme ve bağlı kayıtları silindi."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $error) {
    $db->rollback();

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
