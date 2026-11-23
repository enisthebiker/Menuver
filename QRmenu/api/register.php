
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

// İşletme kayıt iznini kontrol et
$settingsFile = __DIR__ . "/data/registration-settings.json";

$registrationEnabled = false;

if (is_file($settingsFile) && is_readable($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);

    if (
        is_array($settings) &&
        isset($settings["registration_enabled"]) &&
        $settings["registration_enabled"] === true
    ) {
        $registrationEnabled = true;
    }
}

if (!$registrationEnabled) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Yeni işletme kayıtları şu anda kapalı."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$veri = json_decode(file_get_contents("php://input"), true);

if (!is_array($veri)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz veri."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$businessName = trim($veri["businessName"] ?? "");
$username = trim($veri["username"] ?? "");
$email = strtolower(trim($veri["email"] ?? ""));
$password = $veri["password"] ?? "";

if (
    $businessName === "" ||
    $username === "" ||
    $email === "" ||
    $password === ""
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Tüm alanlar zorunludur."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Geçerli bir e-posta adresi girin."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Şifre en az 8 karakter olmalıdır."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Türkçe karakterleri URL uyumlu hale getir
$slug = mb_strtolower($businessName, "UTF-8");

$slug = strtr($slug, [
    "ç" => "c",
    "ğ" => "g",
    "ı" => "i",
    "ö" => "o",
    "ş" => "s",
    "ü" => "u"
]);

$slug = preg_replace("/[^a-z0-9]+/", "-", $slug);
$slug = trim($slug, "-");

if ($slug === "") {
    $slug = "isletme";
}

// E-posta daha önce kullanılmış mı?
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();

    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "Bu e-posta adresi zaten kayıtlı."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->close();

// Slug çakışmasını önle
$kontrol = $db->prepare("SELECT id FROM businesses WHERE slug = ?");
$kontrol->bind_param("s", $slug);
$kontrol->execute();
$kontrol->store_result();

if ($kontrol->num_rows > 0) {
    $slug .= "-" . random_int(1000, 9999);
}

$kontrol->close();

$sifreHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db->begin_transaction();

    // İşletmeyi oluştur
    $stmt = $db->prepare(
        "INSERT INTO businesses (name, slug) VALUES (?, ?)"
    );

    $stmt->bind_param("ss", $businessName, $slug);
    $stmt->execute();

    $businessId = $db->insert_id;
    $stmt->close();

    // Kullanıcı hesabını oluştur
    $stmt = $db->prepare(
        "INSERT INTO users (business_id, username, email, password)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isss",
        $businessId,
        $username,
        $email,
        $sifreHash
    );

    $stmt->execute();
    $stmt->close();

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "İşletme hesabı oluşturuldu."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    $db->rollback();

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Kayıt oluşturulamadı."
    ], JSON_UNESCAPED_UNICODE);
}
