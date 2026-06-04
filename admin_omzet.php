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
    <title>Panel Admin - Rekap Omzet</title>
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
            <h2>💰 Rekapitulasi Arus Kas Omzet Wilayah</h2>
            <p style="color:#64748b; margin-bottom: 15px;">Menerapkan <b>Materi 3: Set Operations (UNION ALL)</b> melalui objek view <code>view_rekap_wilayah</code> untuk menggabungkan dua segmen kelompok wilayah cabang berbeda.</p>
            
            <table>
                <thead>
                    <tr>
                        <th>No. Referensi Buku Kas</th>
                        <th>Wilayah Operasional Jaringan</th>
                        <th>Nilai Uang Masuk Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmtUnion = $pdo->query("SELECT * FROM view_rekap_wilayah");
                    $rowsUnion = $stmtUnion->fetchAll();
                    $grand_total = 0;
                    if (count($rowsUnion) == 0) {
                        echo "<tr><td colspan='3' style='text-align:center;'>Belum ada dana masuk di kas wilayah manapun.</td></tr>";
                    } else {
                        foreach ($rowsUnion as $idx => $u) {
                            $grand_total += $u['total_bayar_bersih'];
                            echo "<tr>
                                    <td>Ref-00{$idx}</td>
                                    <td><b>{$u['wilayah_operasional']}</b></td>
                                    <td style='color:#16a34a; font-weight:600;'>Rp ".number_format($u['total_bayar_bersih'], 0, ',', '.')."</td>
                                  </tr>";
                        }
                        echo "<tr style='background:#f1f5f9; font-weight:bold; font-size:16px;'>
                                <td colspan='2' style='text-align:right;'>TOTAL GABUNGAN OMZET OPERASIONAL:</td>
                                <td style='color:#0284c7;'>Rp ".number_format($grand_total, 0, ',', '.')."</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>