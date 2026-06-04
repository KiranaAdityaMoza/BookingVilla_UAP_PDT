<?php
// Koneksi Database dengan PDO (PHP Data Objects)
$host     = 'localhost';
$db_name  = 'uap_villa';
$username = 'root';
$password = ''; // Kosongkan jika menggunakan bawaan Laragon/XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Mengatur error mode ke Exception untuk menangkap error transaksi/deadlock
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>