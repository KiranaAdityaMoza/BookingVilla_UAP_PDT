<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$status = '';

// KODE BACKUP MANUAL (Bila tombol di klik)
if (isset($_POST['btn_backup'])) {
    $folder_target = __DIR__ . '/backup/';
    if (!is_dir($folder_target)) {
        mkdir($folder_target, 0755, true);
    }

    $nama_file = 'backup_manual_' . date('Y-m-d_H-i') . '_' . time() . '.sql';
    $path_lengkap = $folder_target . $nama_file;
    
    try {
        $tables = ['customer', 'users', 'vila', 'booking', 'log_pembatalan'];
        $sql = "-- Backup Manual UAP Villa " . date('Y-m-d H:i:s') . "\n\n";
        
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
        $message = "💾 Sukses Backup Manual! Berkas tersimpan aman di folder backup/.";
        $status = "success";
    } catch (Exception $e) {
        $message = "Gagal: " . $e->getMessage();
        $status = "danger";
    }
}

// LOGIKA PEMBACAAN LOG / FILE DI FOLDER BACKUP SECARA DINAMIS
$folder_backup = __DIR__ . '/backup/';
$daftar_files = [];

if (is_dir($folder_backup)) {
    $files = scandir($folder_backup);
    foreach ($files as $file) {
        // Hanya ambil file yang berakhiran .sql
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $path_file = $folder_backup . $file;
            $waktu_dibuat = date("d M Y - H:i:s", filemtime($path_file));
            $ukuran = round(filesize($path_file) / 1024, 2) . ' KB';
            
            // Deteksi jenis backup berdasarkan awalan nama filenya
            $tipe = (strpos($file, 'backup_otomatis_') !== false) ? '🤖 Otomatis (Task Scheduler)' : '👨‍💻 Manual Admin';
            
            $daftar_files[] = [
                'nama' => $file,
                'waktu' => $waktu_dibuat,
                'ukuran' => $ukuran,
                'tipe' => $tipe
            ];
        }
    }
    // Urutkan file berdasarkan yang terbaru di atas
    usort($daftar_files, function($a, $b) {
        return filemtime($folder_backup . $b['nama']) - filemtime($folder_backup . $a['nama']);
    });
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
        
        <div class="card" style="margin-bottom: 30px;">
            <h2>🗄️ Pusat Manajemen Cadangan Data (Backup)</h2>
            <p style="color:#64748b; margin-bottom: 20px;">
                Sistem mendeteksi pencadangan data melalui dua metode: eksekusi langsung oleh admin atau otomatisasi terjadwal via <b>Windows Task Scheduler</b>.
            </p>
            <form method="POST">
                <button type="submit" name="btn_backup" class="btn btn-success">Jalankan Backup Manual Sekarang</button>
            </form>
        </div>

        <div class="card">
            <h3>📂 Log & Berkas Hasil Backup di Direktori</h3>
            <p style="color:#64748b; margin-bottom: 15px; font-size: 13px;">Daftar berkas di bawah dibaca langsung dari subfolder <code>/backup/</code> secara real-time.</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Nama Berkas Backup</th>
                        <th>Waktu Eksekusi</th>
                        <th>Ukuran Berkas</th>
                        <th>Metode / Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_files) === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">
                                Belum ada berkas backup (.sql) yang tersimpan di folder cadangan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_files as $f): ?>
                            <tr>
                                <td><code style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($f['nama']) ?></code></td>
                                <td><?= $f['waktu'] ?> WIB</td>
                                <td><span class='badge' style='background: #e2e8f0; color: #334155;'><?= $f['ukuran'] ?></span></td>
                                <td>
                                    <span class="badge" style="background: <?= (strpos($f['tipe'], 'Otomatis') !== false) ? '#dcfce7; color: #15803d;' : '#dbeafe; color: #1d4ed8;'; ?>">
                                        <?= $f['tipe'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>