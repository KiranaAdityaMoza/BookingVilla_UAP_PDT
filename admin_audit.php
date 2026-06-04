<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Log Audit</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { height: 100vh; position: fixed; width: 260px; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .tab-link.active { background-color: #0284c7 !important; color: white !important; font-weight: bold; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2>⚠️ Log Audit Sistem (Kondisi Kritis Pembatalan Dana Lunas)</h2>
            <p style="color:#64748b;">Menerapkan <b>Bonus Materi: Trigger System</b>. Tabel di bawah terisi otomatis oleh trigger <code>trigger_pembatalan_lunas</code> hanya ketika ada pesanan berstatus 'Paid' yang mendadak di-Cancel oleh admin.</p>
            <table>
                <thead>
                    <tr>
                        <th>ID Log</th>
                        <th>ID Booking Asal</th>
                        <th>Nama Penyewa Terkena Dampak</th>
                        <th>Nominal Hangus Masuk Kas</th>
                        <th>Waktu Pencatatan Otomatis Mesin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmtLog = $pdo->query("SELECT * FROM log_pembatalan ORDER BY id_log DESC");
                    $rowsLog = $stmtLog->fetchAll();
                    if (count($rowsLog) == 0) {
                        echo "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>Sistem Aman. Belum ada rekam jejak pembatalan sepihak yang tercatat.</td></tr>";
                    } else {
                        foreach ($rowsLog as $l) {
                            echo "<tr>
                                    <td><span class='badge badge-danger'>LOG-#0{$l['id_log']}</span></td>
                                    <td><b>#B0{$l['id_booking']}</b></td>
                                    <td>{$l['nama_customer']}</td>
                                    <td style='font-weight:bold; color:#dc2626;'>Rp ".number_format($l['nominal_hangus'], 0, ',', '.')."</td>
                                    <td>{$l['tgl_pencatatan']}</td>
                                  </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>