<?php
session_start(); // Mulai sesi

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "login_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Konfigurasi anti-brute force
$maxAttempts = 5; // Maksimum percobaan gagal
$lockoutTime = 15; // Waktu terkunci dalam menit

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input untuk mencegah karakter berbahaya
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    // Cek username dan password statis
    if ($username === "admin" && $password === "admin1234") {
        // Simpan sesi login
        $_SESSION['username'] = $username;

        // Redirect ke halaman utama
        header("Location: warehouse.php");
        exit();
    }

    // Cek apakah user ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Periksa apakah akun terkunci
        if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
            $remainingTime = (strtotime($user['lock_until']) - time()) / 60;
            die("Akun terkunci. Coba lagi dalam " . ceil($remainingTime) . " menit.");
        }

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Reset percobaan gagal
            $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();

            // Simpan sesi login
            $_SESSION['username'] = $username;

            // Redirect ke halaman utama
            header("Location: warehouse.php");
            exit();
        } else {
            // Tambahkan percobaan gagal
            $failedAttempts = $user['failed_attempts'] + 1;
            $lockUntil = null;

            if ($failedAttempts >= $maxAttempts) {
                $lockUntil = date('Y-m-d H:i:s', strtotime("+$lockoutTime minutes"));
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, lock_until = ? WHERE username = ?");
                $stmt->bind_param("iss", $failedAttempts, $lockUntil, $username);
                $stmt->execute();
                die("Terlalu banyak percobaan gagal. Akun terkunci selama $lockoutTime menit.");
            } else {
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE username = ?");
                $stmt->bind_param("is", $failedAttempts, $username);
                $stmt->execute();
                die("Password salah! Percobaan gagal: $failedAttempts/$maxAttempts.");
            }
        }
    } else {
        die("Username tidak ditemukan!");
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
