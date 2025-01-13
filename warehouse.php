<?php
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
    <title>Sistem Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Sistem Gudang</h1>
        <div class="text-end mb-3">
            <a href="add_product.php" class="btn btn-primary">Tambah Produk</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pdo = new PDO("mysql:host=localhost;dbname=warehousesafe_db", "root", "");
                $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
                while ($row = $stmt->fetch()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['product_name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>Rp " . number_format($row['price'], 2, ',', '.') . "</td>
                        <td>
                            <a href='edit_product.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='delete_product.php?id={$row['id']}' class='btn btn-danger btn-sm'>Hapus</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="text-end mb-3">
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
    </div>
</body>
</html>

