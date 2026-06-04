<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$status_type = '';

if (isset($_POST['btn_backup'])) {
    $backup_file = 'backup_uap_villa_' . time() . '.sql';
    try {
        $tables = ['customer', 'users', 'vila', 'booking', 'log_pembatalan'];
        $sql_dump = "-- CADANGAN DATABASE UAP VILLA \n-- Dibuat otomatis: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $t) {
            $rows = $pdo->query("SELECT * FROM $t")->fetchAll();
            $sql_dump .= "-- Data Tabel $t \n";
            foreach ($rows as $r) {
                $values = array_map(function($v) use ($pdo) { return $v === null ? 'NULL' : $pdo->quote($v); }, $r);
                $sql_dump .= "INSERT INTO $t VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql_dump .= "\n";
        }
        file_put_contents($backup_file, $sql_dump);
        $message = "💾 Sukses Backup! Berkas cadangan aman disimpan dengan nama: <b>$backup_file</b>";
        $status_type = "success";
    } catch (Exception $e) {
        $message = "Backup gagal: " . $e->getMessage();
        $status_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Pemeliharaan & Backup</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .sidebar a.active { background-color: #0284c7 !important; color: white !important; font-weight: bold; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #475569; border-radius: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>💾 Pemeliharaan, Pencadangan Instan & Task Scheduler</h2>
            <p style="color:#64748b; margin-bottom:20px;">Menerapkan <b>Bonus Materi: Backup Database & Dokumentasi Task Scheduler</b> demi kelangsungan keamanan data.</p>
            
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:20px; border-radius:6px; margin-bottom:25px;">
                <h3>⚡ Fitur Backup 1-Klik</h3>
                <p style="font-size:14px; color:#166534; margin-bottom:15px;">Klik tombol di bawah untuk membuat file kloning data <code>.sql</code> di dalam direktori folder Laragon secara instan.</p>
                <form action="" method="POST">
                    <button type="submit" name="btn_backup" class="btn btn-success">Mulai Proses Backup Database Sekarang</button>
                </form>
            </div>

            <div style="background:#fafafa; border:1px solid #e2e8f0; padding:20px; border-radius:6px;">
                <h3>🕒 Implementasi Task Scheduler / Cron Job (Dokumentasi Arsitektur)</h3>
                <p style="font-size:14px; color:#475569; margin-bottom:10px;">Untuk melakukan otomasi pembersihan data sampah, tim pengembang memasang skrip Event Scheduler di server MySQL yang berjalan otomatis setiap 24 jam sekali:</p>
                <pre style="background:#1e293b; color:#38bdf8; padding:15px; border-radius:6px; overflow-x:auto; font-family:monospace; font-size:13px;">
CREATE EVENT IF NOT EXISTS ev_bersihkan_sampah_booking
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM booking 
  WHERE status_booking = 'Pending' 
  AND tgl_checkin < NOW();</pre>
            </div>
        </div>
    </div>
</body>
</html>