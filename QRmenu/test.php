<?php

$baglanti = new mysqli("localhost", "root", "", "qrmenu");

if ($baglanti->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $baglanti->connect_error);
}

echo "MySQL bağlantısı başarılı!";

?>