<?php
session_start();

// Konfigurasi database
$host = '127.0.0.1';
$db = 'chatsafe_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Koneksi gagal: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi username
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        echo "Username tidak valid! Hanya boleh berisi huruf, angka, dan underscore (3-30 karakter).";
        exit;
    }

    // Validasi password
    if (strlen($password) < 6) {
        echo "Password harus minimal 6 karakter.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $hashedPassword]);
        echo "Signup berhasil! Silakan login.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error kode untuk duplicate entry
            echo "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            echo "Gagal mendaftar: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Signup</h2>
        <form action="signup.php" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
                <small class="form-text text-muted">Hanya boleh berisi huruf, angka, dan underscore (3-30 karakter).</small>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small class="form-text text-muted">Minimal 6 karakter.</small>
            </div>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
        <p class="mt-3">Sudah punya akun? <a href="index.html">Login di sini</a>.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
