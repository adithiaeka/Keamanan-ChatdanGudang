<?php
$pdo = new PDO("mysql:host=localhost;dbname=warehousesafe_db", "root", "");

// Ambil data produk yang akan diedit
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE products SET product_name = ?, quantity = ?, price = ? WHERE id = ?");
    $stmt->execute([$_POST['product_name'], $_POST['quantity'], $_POST['price'], $_POST['id']]);
    header("Location: warehouse.php");
    exit();
}
// sesion
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Waktu sesi maksimal (20 menit)
$max_session_time = 20 * 60; // 20 menit dalam detik
$warning_time = 10 * 60; // 10 menit untuk peringatan

// Jika sesi baru dibuat, atur waktu sesi awal
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Cek waktu timeout sesi
$inactive_time = time() - $_SESSION['last_activity'];
if ($inactive_time > $max_session_time) {
    // Hancurkan sesi jika melebihi 20 menit
    session_destroy();
    header("Location: index.php?message=Sesi Anda telah berakhir.");
    exit();
} elseif ($inactive_time > $warning_time) {
    // Jika waktu tidak aktif melebihi 10 menit, tampilkan peringatan
    echo "<script>
        let extendSession = confirm('Anda tidak aktif selama 10 menit. Apakah ingin memperpanjang sesi?');
        if (extendSession) {
            window.location.href = 'extend_session.php';
        } else {
            window.location.href = 'logout.php';
        }
    </script>";
}

// Perbarui waktu terakhir aktivitas
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit Produk</h1>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product['product_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Jumlah</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="warehouse.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
