<?php

$baglanti = new mysqli("localhost", "root", "", "qrmenu");
$baglanti->set_charset("utf8mb4");

$sql = "INSERT INTO products (name, category, price, description, active)
        VALUES ('Karışık Tost', 'Kahvaltı', 150.00, 'Kaşarlı ve sucuklu tost', 1)";

if ($baglanti->query($sql)) {
    echo "Ürün başarıyla eklendi!";
} else {
    echo "Hata: " . $baglanti->error;
}

$baglanti->close();

?>