<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_POST['btn_backup'])) {
    $folder_target = __DIR__ . '/backup/';
    if (!is_dir($folder_target)) {
        mkdir($folder_target, 0755, true);
    }

    $nama_file = 'backup_manual_' . date('Y-m-d_H-i') . '.sql';
    $path_lengkap = $folder_target . $nama_file;
    
    try {
        $tables = ['customer', 'users', 'vila', 'booking', 'log_pembatalan'];
        $sql = "-- Backup Manual UAP Villa " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $t) {
            $data = $pdo->query("SELECT * FROM $t")->fetchAll();
            $sql .= "-- Tabel: $t\n";
            foreach ($data as $r) {
                $v = array_map(function($i) use ($pdo) { return $i === null ? 'NULL' : $pdo->quote($i); }, $r);
                $sql .= "INSERT INTO $t VALUES (" . implode(', ', $v) . ");\n";
            }
            $sql .= "\n";
        }
        
        file_put_contents($path_lengkap, $sql);
        
        $_SESSION['status_backup'] = "Sukses! Berkas backup berhasil disimpan.";
        header("Location: admin_backup.php"); 
        exit; 
    } catch (Exception $e) {
        $_SESSION['status_backup'] = "Gagal: " . $e->getMessage();
        header("Location: admin_backup.php");
        exit;
    }
}

$folder_backup = __DIR__ . '/backup/';
$daftar_files = [];

if (is_dir($folder_backup)) {
    $files = scandir($folder_backup);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $path_file = $folder_backup . $file;
            $daftar_files[] = [
                'nama' => $file,
                'waktu' => date("d M Y - H:i:s", filemtime($path_file)),
                'ukuran' => round(filesize($path_file) / 1024, 2) . ' KB'
            ];
        }
    }
    usort($daftar_files, function($a, $b) use ($folder_backup) {
        return filemtime($folder_backup . $b['nama']) - filemtime($folder_backup . $a['nama']);
    });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Backup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <?php if (isset($_SESSION['status_backup'])): ?>
            <div class="alert"><?= $_SESSION['status_backup']; unset($_SESSION['status_backup']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>🗄️ Manajemen Backup Database</h2>
            <form method="POST">
                <button type="submit" name="btn_backup" class="btn btn-success">Jalankan Backup Sekarang</button>
            </form>
        </div>

        <div class="card">
            <h3>📂 Berkas Backup</h3>
            <table>
                <thead>
                    <tr><th>Nama Berkas</th><th>Waktu</th><th>Ukuran</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_files as $f): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($f['nama']) ?></code></td>
                            <td><?= $f['waktu'] ?></td>
                            <td><?= $f['ukuran'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>