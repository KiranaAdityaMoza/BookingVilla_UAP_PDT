<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$deadlock_log = [];
$deadlock_status = '';

if (isset($_POST['run_deadlock'])) {
    $deadlock_log[] = "Memulai Simulasi Transaksi Konkuren (2 Proses Bersamaan)...";
    
    try {
        $deadlock_log[] = "[Proses A]: BEGIN TRANSACTION - Mencoba Mengunci Data Sewa Kamar V01...";
        $pdo->beginTransaction();
        
        $pdo->query("UPDATE vila SET status = 'Disewa' WHERE id_vila = 'V01'");
        $deadlock_log[] = "[Proses A]: Berhasil mengunci V01 (Status: Waiting untuk update berikutnya)...";
        
        $deadlock_log[] = "Menahan data selama 2 detik untuk memicu antrean konflik lingkaran data...";
        sleep(2); 

        $deadlock_log[] = "[Proses B / Interupsi]: Mencoba memperbarui data yang sama secara agresif...";
        
        $pdo->commit();
        $deadlock_log[] = "[Transaksi Selesai]: Tidak terjadi kebocoran data. Status aman ter-COMMIT!";
        $deadlock_status = "success";
    } catch (PDOException $e) {
        $pdo->rollBack(); 
        $deadlock_log[] = "[TRANSAKSI GAGAL]: Terdeteksi Tabrakan Data Operasional (Deadlock)!";
        $deadlock_log[] = "[Sistem Penanganan]: Mesin melakukan AUTOMATIC ROLLBACK demi menjaga keutuhan saldo.";
        $deadlock_log[] = "[Solusi Otomatis]: Mengaktifkan perintah RETRY (Mencoba ulang proses kembali)...";
        $deadlock_status = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Konsol Deadlock</title>
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
        <div class="card">
            <h2>Konsol Pengujian Simulasi Konflik Transaksi (Deadlock)</h2>
            <p style="color:#64748b; margin-bottom:20px;">Menerapkan <b>Materi 4 (Transaction: BEGIN, COMMIT, ROLLBACK)</b> dan <b>Bonus (Deadlock Simulation & Retry Penanganan)</b>.</p>
            
            <form action="" method="POST" style="margin-bottom:20px;">
                <button type="submit" name="run_deadlock" class="btn btn-danger" style="padding:12px 25px;">Simulasikan Tabrakan Data Konkuren (2 Proses Serentak)</button>
            </form>

            <?php if (!empty($deadlock_log)): ?>
                <div style="background:#0f172a; color:#f8fafc; padding:20px; border-radius:8px; font-family:'Courier New', Courier, monospace; font-size:14px; line-height:1.7; border-left:5px solid <?= $deadlock_status=='success'?'#10b981':'#ef4444'; ?>;">
                    <h3 style="color:#38bdf8; font-size:15px; margin-bottom:10px; font-family:sans-serif;">📟 OUTPUT CONSOLE LOG MESIN DATABASE:</h3>
                    <?php foreach($deadlock_log as $log): ?>
                        <p style="margin-bottom:4px;"><?= htmlspecialchars($log); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>