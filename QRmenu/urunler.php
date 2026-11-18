
<?php

$baglanti = new mysqli("localhost", "root", "", "qrmenu");
$baglanti->set_charset("utf8mb4");

$sql = "SELECT * FROM products ORDER BY id DESC";
$sonuc = $baglanti->query($sql);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Listesi</title>
</head>
<body>

<h2>Ürün Listesi</h2>

<?php while ($urun = $sonuc->fetch_assoc()): ?>

    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <h3><?php echo htmlspecialchars($urun["name"]); ?></h3>
        <p>Kategori: <?php echo htmlspecialchars($urun["category"]); ?></p>
        <p>Fiyat: <?php echo number_format($urun["price"], 2, ',', '.'); ?> TL</p>
        <p><?php echo htmlspecialchars($urun["description"]); ?></p>
        <a href="duzenle.php?id=<?php echo $urun['id']; ?>">Düzenle</a>
        <a href="sil.php?id=<?php echo $urun['id']; ?>"
   onclick="return confirm('Bu ürünü silmek istediğine emin misin?')">
   Sil
</a>
    </div>

<?php endwhile; ?>

</body>
</html>

<?php
$baglanti->close();
?>
