<?php
session_start();
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
    die("Koneksi gagal: " . $e->getMessage());
}

// Konfigurasi pembatasan login
$maxAttempts = 5; // Maksimum percobaan login
$lockoutTime = 15; // Waktu terkunci dalam menit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah user ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Cek apakah user terkunci
        if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
            $remainingTime = (strtotime($user['lock_until']) - time()) / 60;
            die("Akun terkunci. Coba lagi dalam " . ceil($remainingTime) . " menit.");
        }

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Reset percobaan gagal saat login berhasil
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE username = ?");
            $stmt->execute([$username]);

            $_SESSION['username'] = $username;
            header("Location: chat.php");
            exit();
        } else {
            // Tambah jumlah percobaan gagal
            $failedAttempts = $user['failed_attempts'] + 1;
            $lockUntil = null;

            if ($failedAttempts >= $maxAttempts) {
                $lockUntil = date('Y-m-d H:i:s', strtotime("+$lockoutTime minutes"));
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, lock_until = ? WHERE username = ?");
                $stmt->execute([$failedAttempts, $lockUntil, $username]);
                die("Terlalu banyak percobaan gagal. Akun terkunci selama $lockoutTime menit.");
            } else {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE username = ?");
                $stmt->execute([$failedAttempts, $username]);
                die("Username atau password salah! Percobaan gagal: $failedAttempts/$maxAttempts.");
            }
        }
    } else {
        echo "Username atau password salah!";
    }
}
?>
 