<?php
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

// Query pesan
$stmt = $pdo->query("SELECT username, message, created_at FROM messages ORDER BY created_at ASC");

// Menampilkan pesan
while ($row = $stmt->fetch()) {
    // Sanitasi output
    $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
    $timestamp = htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8');

    // Format dan tampilkan pesan
    echo "<p><strong>{$username}:</strong> {$message} <small><i>{$timestamp}</i></small></p>";
}
?>
