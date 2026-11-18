
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

// Bütün işletmeleri listele
$result = $db->query(
    "SELECT
        businesses.id,
        businesses.name,
        businesses.slug,
        businesses.created_at,
        COUNT(users.id) AS user_count
     FROM businesses
     LEFT JOIN users
        ON users.business_id = businesses.id
     GROUP BY
        businesses.id,
        businesses.name,
        businesses.slug,
        businesses.created_at
     ORDER BY businesses.id ASC"
);

$businesses = [];

while ($row = $result->fetch_assoc()) {
    $businesses[] = $row;
}

echo json_encode([
    "success" => true,
    "businesses" => $businesses
], JSON_UNESCAPED_UNICODE);
