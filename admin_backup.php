<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$status = '';

if (isset($_POST['btn_backup'])) {
    // Definisi folder tujuan yang absolut
    $folder_target = __DIR__ . '/backup/';
    
    // Pastikan folder ada
    if (!is_dir($folder_target)) {
        mkdir($folder_target, 0755, true);
    }

    $nama_file = 'backup_uap_villa_' . time() . '.sql';
    $path_lengkap = $folder_target . $nama_file;
    
    try {
        $tables = ['customer', 'users', 'vila', 'booking', 'log_pembatalan'];
        $sql = "-- Backup UAP Villa " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $t) {
            $data = $pdo->query("SELECT * FROM $t")->fetchAll();
            $sql .= "-- Data: $t\n";
            foreach ($data as $r) {
                $v = array_map(function($i) use ($pdo) { return $i === null ? 'NULL' : $pdo->quote($i); }, $r);
                $sql .= "INSERT INTO $t VALUES (" . implode(', ', $v) . ");\n";
            }
            $sql .= "\n";
        }
        
        file_put_contents($path_lengkap, $sql);
        $message = "Berhasil backup: " . $nama_file;
        $status = "success";
    } catch (Exception $e) {
        $message = "Gagal: " . $e->getMessage();
        $status = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Backup Database</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-<?= $status ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Manajemen Backup Database</h2>
            <form method="POST">
                <button type="submit" name="btn_backup" class="btn btn-success">Jalankan Backup Sekarang</button>
            </form>
        </div>
    </div>
</body>
</html>