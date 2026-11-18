
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

// Yalnızca giriş yapmış işletme kullanıcıları erişebilir
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["business_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "business"
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

$veri = json_decode(file_get_contents("php://input"), true);
$password = $veri["password"] ?? "";

if ($password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Şifrenizi girmeniz gerekiyor."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$_SESSION["user_id"];
$businessId = (int)$_SESSION["business_id"];

try {
    $db->begin_transaction();

    // Kullanıcının şifresini ve işletme bağlantısını doğrula
    $stmt = $db->prepare(
        "SELECT password FROM users
         WHERE id = ? AND business_id = ? AND role = 'business'
         LIMIT 1 FOR UPDATE"
    );

    $stmt->bind_param("ii", $userId, $businessId);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user["password"])) {
        throw new Exception("Şifre hatalı veya kullanıcı bulunamadı.");
    }

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

    // İşletmeye bağlı verileri sil
    $tables = [
        "products",
        "categories",
        "restaurant_settings",
        "users"
    ];

    foreach ($tables as $table) {
        $stmt = $db->prepare(
            "DELETE FROM $table WHERE business_id = ?"
        );

        $stmt->bind_param("i", $businessId);
        $stmt->execute();
        $stmt->close();
    }

    // İşletmeyi sil
    $stmt = $db->prepare(
        "DELETE FROM businesses WHERE id = ?"
    );

    $stmt->bind_param("i", $businessId);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    // Kullanıcının oturumunu kapat
    $_SESSION = [];
    session_destroy();

    echo json_encode([
        "success" => true,
        "message" => "Hesabınız ve işletmeniz silindi."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $error) {
    $db->rollback();

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
