
<?php

// Veritabanı bağlantısı
$db = new mysqli("localhost", "root", "", "qrmenu");

if ($db->connect_error) {
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Veritabanı bağlantısı kurulamadı."
    ], JSON_UNESCAPED_UNICODE));
}

$db->set_charset("utf8mb4");


// Oturum başlatma

function startUserSession() {
    if (session_status() === PHP_SESSION_NONE) {

        ini_set("session.use_strict_mode", "1");

        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax"
        ]);

        session_start();
    }
}




// Giriş yapan kullanıcının işletme ID'sini alma

function getBusinessId() {
    startUserSession();

    // Developer yalnızca seçtiği işletmeyi yönetebilir.
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        $businessId = (int)($_SESSION["selected_business_id"] ?? 0);

        if ($businessId > 0) {
            return $businessId;
        }
    }

    // Normal işletme kullanıcısı yalnızca kendi işletmesini yönetebilir.
    elseif (isset($_SESSION["role"]) && $_SESSION["role"] === "business") {
        $businessId = (int)($_SESSION["business_id"] ?? 0);

        if ($businessId > 0) {
            return $businessId;
        }
    }

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Bu işlem için yetkiniz yok."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

