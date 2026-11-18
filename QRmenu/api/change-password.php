
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Desteklenmeyen işlem."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kullanıcı giriş yapmış olmalı
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Oturum açmanız gerekiyor."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$veri = json_decode(file_get_contents("php://input"), true);

$currentPassword = $veri["currentPassword"] ?? "";
$newPassword = $veri["newPassword"] ?? "";

if (!is_string($currentPassword) || !is_string($newPassword) ||
    $currentPassword === "" || $newPassword === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Şifre alanları zorunludur."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (preg_match('/^.{8,}$/us', $newPassword) !== 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Yeni şifre en az 8 karakter olmalıdır."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$_SESSION["user_id"];

// Mevcut şifreyi veritabanından al
$stmt = $db->prepare(
    "SELECT password FROM users WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user || !password_verify($currentPassword, $user["password"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Mevcut şifreniz hatalı."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yeni şifreyi güvenli biçimde hashle
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $db->prepare(
    "UPDATE users SET password = ? WHERE id = ?"
);

$stmt->bind_param("si", $hashedPassword, $userId);
$stmt->execute();

$stmt->close();

session_regenerate_id(true);

echo json_encode([
    "success" => true,
    "message" => "Şifreniz başarıyla değiştirildi."
], JSON_UNESCAPED_UNICODE);

?>
