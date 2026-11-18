
<?php

require_once __DIR__ . "/config.php";

startUserSession();

// Oturum çerezinin bilgilerini al
$params = session_get_cookie_params();

// Oturum verilerini temizle
$_SESSION = [];

// Tarayıcıdaki oturum çerezini de sil
if (ini_get("session.use_cookies")) {
    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"]
        ]
    );
}

session_destroy();

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "success" => true,
    "message" => "Oturum kapatıldı."
], JSON_UNESCAPED_UNICODE);

?>
