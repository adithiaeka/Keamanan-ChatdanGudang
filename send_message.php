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
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
        $message = trim($_POST['message']);

        // Validasi input
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            echo "Username tidak valid! Hanya boleh berisi huruf, angka, dan underscore (3-30 karakter).";
            exit;
        }

        if (empty($message) || mb_strlen($message) > 500) {
            echo "Pesan kosong atau terlalu panjang (maksimal 500 karakter).";
            exit;
        }

        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->execute([$username, htmlspecialchars($message, ENT_QUOTES, 'UTF-8')]);
    } else {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Validasi JSON
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['message'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Format JSON tidak valid']);
            exit;
        }

        $message = trim($data['message']);
        $username = 'gudang';

        if (empty($message) || mb_strlen($message) > 500) {
            echo json_encode(['status' => 'error', 'message' => 'Pesan kosong atau terlalu panjang.']);
            exit;
        }

        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->execute([$username, htmlspecialchars($message, ENT_QUOTES, 'UTF-8')]);
    }
}
?>
