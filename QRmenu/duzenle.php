
<?php

$baglanti = new mysqli("localhost", "root", "", "qrmenu");
$baglanti->set_charset("utf8mb4");

$mesaj = "";

$id = (int)($_POST["id"] ?? $_GET["id"] ?? 0);

// Kaydet düğmesine basıldıysa
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ad = $_POST["name"];
    $kategori = $_POST["category"];
    $fiyat = (float)$_POST["price"];
    $aciklama = $_POST["description"];

    $stmt = $baglanti->prepare(
        "UPDATE products SET name=?, category=?, price=?, description=? WHERE id=?"
    );

    $stmt->bind_param("ssdsi", $ad, $kategori, $fiyat, $aciklama, $id);

    if ($stmt->execute()) {
        $mesaj = "Ürün başarıyla güncellendi!";
    } else {
        $mesaj = "Güncelleme sırasında hata oluştu.";
    }

    $stmt->close();
}

// Güncel ürün bilgilerini getir
$stmt = $baglanti->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$sonuc = $stmt->get_result();
$urun = $sonuc->fetch_assoc();

$stmt->close();

if (!$urun) {
    die("Ürün bulunamadı.");
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle</title>
</head>
<body>

<h2>Ürün Düzenle</h2>

<p><?php echo htmlspecialchars($mesaj); ?></p>

<form method="POST">

    <input type="hidden" name="id" value="<?php echo $urun["id"]; ?>">

    <p>Ürün Adı</p>
    <input type="text" name="name" value="<?php echo htmlspecialchars($urun["name"]); ?>" required>

    <p>Kategori</p>
    <input type="text" name="category" value="<?php echo htmlspecialchars($urun["category"]); ?>" required>

    <p>Fiyat</p>
    <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($urun["price"]); ?>" required>

    <p>Açıklama</p>
    <textarea name="description"><?php echo htmlspecialchars($urun["description"]); ?></textarea>

    <br><br>
    <button type="submit">Değişiklikleri Kaydet</button>

</form>

<br>
<a href="urunler.php">Ürün Listesine Dön</a>

</body>
</html>

<?php
$baglanti->close();
?>
