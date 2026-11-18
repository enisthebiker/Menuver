
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Desteklenmeyen işlem."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$veri = json_decode(file_get_contents("php://input"), true);

$email = strtolower(trim($veri["email"] ?? ""));
$password = $veri["password"] ?? "";

if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "E-posta ve şifre zorunludur."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $db->prepare(
    "SELECT users.id, users.business_id, users.username,
        users.password, users.role, businesses.name
     FROM users
LEFT JOIN businesses
    ON businesses.id = users.business_id
WHERE users.email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$sonuc = $stmt->get_result();
$user = $sonuc->fetch_assoc();

$stmt->close();

if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "E-posta veya şifre hatalı."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Güvenli oturum başlat
startUserSession();
session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["id"];
if ($user["business_id"] !== null) {
    $_SESSION["business_id"] = (int)$user["business_id"];
} else {
    unset($_SESSION["business_id"]);
}
$_SESSION["username"] = $user["username"];
$_SESSION["business_name"] = $user["name"];
$_SESSION["role"] = $user["role"];
echo json_encode([
    "success" => true,
    "message" => "Giriş başarılı.",
    "role" => $user["role"]
], JSON_UNESCAPED_UNICODE);
