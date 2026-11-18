
<?php
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $baglanti = new mysqli("localhost", "root", "", "qrmenu");
    $baglanti->set_charset("utf8mb4");

    $ad = $_POST["name"];
    $kategori = $_POST["category"];
    $fiyat = $_POST["price"];
    $aciklama = $_POST["description"];

    $sql = "INSERT INTO products (name, category, price, description)
            VALUES (?, ?, ?, ?)";

    $stmt = $baglanti->prepare($sql);
    $stmt->bind_param("ssds", $ad, $kategori, $fiyat, $aciklama);

    if ($stmt->execute()) {
        $mesaj = "Ürün başarıyla kaydedildi!";
    } else {
        $mesaj = "Kayıt sırasında hata oluştu.";
    }

    $stmt->close();
    $baglanti->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Ekle</title>
</head>
<body>

    <h2>Yeni Ürün Ekle</h2>

    <form method="POST">

        <p>Ürün Adı</p>
        <input type="text" name="name" required>

        <p>Kategori</p>
        <input type="text" name="category" required>

        <p>Fiyat</p>
        <input type="number" name="price" step="0.01" required>

        <p>Açıklama</p>
        <textarea name="description"></textarea>

        <br><br>
        <button type="submit">Ürünü Kaydet</button>

    </form>

    <p><?php echo htmlspecialchars($mesaj); ?></p>

</body>
</html>
