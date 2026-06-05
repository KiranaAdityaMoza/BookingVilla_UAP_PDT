<?php
require_once 'config.php';
session_start();
session_write_close();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ================= DEADLOCK LOGIC (TETAP ASLI) =================
if (isset($_GET['proses'])) {
    $proses = $_GET['proses'];

    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Console Proses $proses</title>";

    echo "<style>
        body{
            margin:0;
            font-family:'Poppins', sans-serif;
            background:#0f172a;
            color:#e2e8f0;
            padding:30px;
            line-height:1.8;
        }
        h2{color:#38bdf8;}
        .ok{color:#22c55e;}
        .err{color:#ef4444;}
        .info{color:#94a3b8;}
    </style></head><body>";

    if ($proses === 'A') {
        echo "<h2>⚙️ Proses A (LOCK V01 → CUSTOMER)</h2><hr>";

        try {
            $pdo->beginTransaction();

            $pdo->exec("UPDATE vila SET status = status WHERE id_vila = 'V01'");
            echo "<div class='ok'>🔒 Lock Vila V01 berhasil</div>";

            echo "<div class='info'>⏳ Menahan transaksi 10 detik...</div>";
            ob_flush(); flush();
            sleep(10);

            $pdo->exec("UPDATE customer SET nama = nama WHERE id_customer = 1");

            $pdo->commit();
            echo "<br><div class='ok'><b>COMMIT SUCCESS PROSES A</b></div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='err'><b>ROLLBACK PROSES A (DEADLOCK)</b></div>";
            echo "<div class='info'>".$e->getMessage()."</div>";
        }
    }

    elseif ($proses === 'B') {
        echo "<h2>⚙️ Proses B (LOCK CUSTOMER → V01)</h2><hr>";

        try {
            $pdo->beginTransaction();

            $pdo->exec("UPDATE customer SET nama = nama WHERE id_customer = 1");
            echo "<div class='ok'>🔒 Lock Customer berhasil</div>";

            $pdo->exec("UPDATE vila SET status = status WHERE id_vila = 'V01'");

            $pdo->commit();
            echo "<br><div class='ok'><b>COMMIT SUCCESS PROSES B</b></div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='err'><b>ROLLBACK PROSES B (DEADLOCK)</b></div>";
            echo "<div class='info'>".$e->getMessage()."</div>";
        }
    }

    echo "</body></html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villaku - Deadlock Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="sidebar">
        <h2>🏡 Villaku</h2>
        <p class="sidebar-subtitle">Admin Panel</p>
        <hr class="sidebar-divider">
        
        <a href="admin_dashboard.php?tab=manajemen_vila" class="tab-link">Master Vila</a>
        <a href="admin_dashboard.php?tab=vila_pantai" class="tab-link">Katalog Wilayah</a>
        <a href="admin_dashboard.php?tab=reservasi_global" class="tab-link">Validasi Reservasi</a>
        <a href="admin_dashboard.php?tab=rekap_omzet" class="tab-link">Rekap Kas</a>
        <a href="admin_dashboard.php?tab=log_audit" class="tab-link">Log Audit</a>
        <a href="admin_dashboard.php?tab=pemeliharaan" class="tab-link">Backup</a>
        
        <a href="admin_deadlock.php" class="tab-link active">Deadlock Console</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="main-content">

        <div class="card header">
            <h1>⚡ Deadlock Simulation Console</h1>
            <p>Sistem pengujian konflik transaksi database (SBD Terdistribusi)</p>
        </div>

        <div class="card animate-fade-up" style="padding: 35px 40px;">
            <h3 style="margin-bottom: 20px; font-weight: 700;">📌 Cara Menjalankan Simulasi</h3>
            
            <div class="instruction-step-wrapper" style="margin-bottom: 30px;">
                <div class="step-item" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="step-number" style="font-size: 20px; font-weight: 800; color: var(--almond); background: var(--almond-light); padding: 4px 14px; border-radius: 12px; border: 1px solid var(--border-color); line-height: 1.3;">01</div>
                    <div class="step-text" style="padding-top: 2px;">
                        <p style="color: var(--text-dark); font-size: 14.5px;">Jalankan <b>Proses A</b> terlebih dahulu melalui tombol di bawah.</p>
                    </div>
                </div>
                
                <div class="step-item" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div class="step-number" style="font-size: 20px; font-weight: 800; color: var(--almond); background: var(--almond-light); padding: 4px 14px; border-radius: 12px; border: 1px solid var(--border-color); line-height: 1.3;">02</div>
                    <div class="step-text" style="padding-top: 2px;">
                        <p style="color: var(--text-dark); font-size: 14.5px;">Segera jalankan <b>Proses B</b> di tab browser baru secara bersamaan.</p>
                    </div>
                </div>
                
                <div class="step-item" style="display: flex; gap: 20px;">
                    <div class="step-number" style="font-size: 20px; font-weight: 800; color: var(--almond); background: var(--almond-light); padding: 4px 14px; border-radius: 12px; border: 1px solid var(--border-color); line-height: 1.3;">03</div>
                    <div class="step-text" style="padding-top: 2px;">
                        <p style="color: var(--text-dark); font-size: 14.5px;">Amati konsol mesin database untuk melihat respon <b>COMMIT SUCCESS</b> atau pemicu otomatis <b>ROLLBACK (Deadlock)</b>.</p>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <a class="btn-primary" target="_blank" href="?proses=A" style="width: 100%; padding: 14px; text-align: center; border-radius: var(--radius-md); text-decoration: none;">
                    Jalankan Proses A
                </a>

                <a class="btn-success-luxury" target="_blank" href="?proses=B" style="width: 100%; padding: 14px; text-align: center; border-radius: var(--radius-md); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; justify-content: center;">
                    Jalankan Proses B
                </a>
            </div>
        </div>

    </div>

</body>
</html>