
<?php

$baglanti = new mysqli("localhost", "root", "", "qrmenu");

$id = (int)($_GET["id"] ?? 0);

$stmt = $baglanti->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: urunler.php");
    exit;
} else {
    echo "Silme işlemi başarısız: " . $baglanti->error;
}

$stmt->close();
$baglanti->close();

?>
