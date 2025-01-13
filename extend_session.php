<?php
session_start();

// Perpanjang sesi dengan memperbarui waktu terakhir aktivitas
$_SESSION['last_activity'] = time();
header("Location: warehouse.php");
exit();
?>
