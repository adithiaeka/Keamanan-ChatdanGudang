<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('form').addEventListener('submit', function(event) {
                const productName = document.getElementById('product_name').value.trim();
                const quantity = parseInt(document.getElementById('quantity').value, 10);
                const price = parseFloat(document.getElementById('price').value);

                if (!productName || productName.length > 100) {
                    alert('Nama produk tidak valid! Maksimal 100 karakter.');
                    event.preventDefault();
                } else if (isNaN(quantity) || quantity <= 0) {
                    alert('Jumlah harus berupa angka positif.');
                    event.preventDefault();
                } else if (isNaN(price) || price <= 0) {
                    alert('Harga harus berupa angka positif.');
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Tambah Produk</h1>
        <form method="POST" action="">
            <?php
            session_start();
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Jumlah</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="warehouse.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validasi CSRF Token
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                throw new Exception('CSRF token tidak valid!');
            }

            // Buat koneksi ke database
            $pdo = new PDO("mysql:host=localhost;dbname=warehousesafe_db", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Ambil data dari form dan sanitasi input
            $product_name = htmlspecialchars(strip_tags($_POST['product_name']));
            $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

            if (!$product_name || !$quantity || !$price) {
                throw new Exception('Input tidak valid!');
            }

            // Masukkan data ke database
            $stmt = $pdo->prepare("INSERT INTO products (product_name, quantity, price) VALUES (?, ?, ?)");
            $stmt->execute([$product_name, $quantity, $price]);

            // Kirim notifikasi ke sistem chat
            $chat_url = 'http://localhost/chatst_secure/send_message.php';  
            $data = [
                'message' => "Dapatkan produk terbaru dari kami $product_name dengan harga spesial Rp $price. BURUAN stock cuma $quantity "
            ];
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data),
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($chat_url, false, $context);

            // Periksa respons dari sistem chat
            if ($response === FALSE) {
                error_log("Gagal mengirim notifikasi ke sistem chat.");
            }

            // Redirect ke halaman utama setelah berhasil
            header("Location: warehouse.php");
            exit();
        } catch (Exception $e) {
            // Tangani kesalahan
            echo "<div class='alert alert-danger'>Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
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
</body>
</html>
