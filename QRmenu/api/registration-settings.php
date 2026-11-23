
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

startUserSession();

$settingsFile = __DIR__ . "/data/registration-settings.json";

// Ayarı oku
function getRegistrationSetting($file) {
    if (!file_exists($file)) {
        return false;
    }

    $content = file_get_contents($file);
    $settings = json_decode($content, true);

    if (
        !is_array($settings) ||
        !isset($settings["registration_enabled"]) ||
        !is_bool($settings["registration_enabled"])
    ) {
        return false;
    }

    return $settings["registration_enabled"];
}

// Ayarı görüntüleme
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    echo json_encode([
        "success" => true,
        "enabled" => getRegistrationSetting($settingsFile)
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Ayarı değiştirme
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Yalnızca Developer değiştirebilir
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

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !is_array($data) ||
        !isset($data["enabled"]) ||
        !is_bool($data["enabled"])
    ) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Geçersiz ayar."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $result = file_put_contents(
        $settingsFile,
        json_encode([
            "registration_enabled" => $data["enabled"]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );

    if ($result === false) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Ayar kaydedilemedi."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    echo json_encode([
        "success" => true,
        "enabled" => $data["enabled"],
        "message" => "Kayıt ayarı güncellendi."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

http_response_code(405);

echo json_encode([
    "success" => false,
    "message" => "Desteklenmeyen işlem."
], JSON_UNESCAPED_UNICODE);

?>
