<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$id_customer = $_SESSION['id_customer'];

// [MATERI 2: SQL JOIN]
$queryHistory = "SELECT b.*, v.nama_vila, v.klaster 
                 FROM booking b 
                 INNER JOIN vila v ON b.id_vila = v.id_vila 
                 WHERE b.id_customer = :id_cust 
                 ORDER BY b.id_booking DESC";
$stmtHistory = $pdo->prepare($queryHistory);
$stmtHistory->execute(['id_cust' => $id_customer]);
$historyData = $stmtHistory->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan - Jaringan Vila</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'customer_navbar.php'; ?>

    <div class="container">
        <div class="card">
            <h2 style="color: #1e293b; margin-bottom: 5px;">📅 Riwayat Pemesanan Kamar Kamu</h2>
            <p style="color: #64748b; margin-bottom: 20px;">Menerapkan <b>Materi 2: SQL JOIN</b> antara tabel booking dan tabel master vila di database.</p>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID Booking</th>
                            <th>Nama Properti Vila</th>
                            <th>Klaster Wilayah</th>
                            <th>Tanggal Check-In</th>
                            <th>Durasi</th>
                            <th>Harga Bruto</th>
                            <th>Potongan Diskon</th>
                            <th>Total Bayar (Net)</th>
                            <th>Status Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($historyData) === 0): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #94a3b8;">Kamu belum pernah melakukan pemesanan vila.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historyData as $h): ?>
                                <tr>
                                    <td><b>#B0<?= $h['id_booking']; ?></b></td>
                                    <td><?= htmlspecialchars($h['nama_vila']); ?></td>
                                    <td><span class="badge" style="background:#f1f5f9;"><?= $h['klaster']; ?></span></td>
                                    <td><?= date('d M Y', strtotime($h['tgl_checkin'])); ?></td>
                                    <td><?= $h['durasi_malam']; ?> Malam</td>
                                    <td>Rp <?= number_format($h['total_harga_asli'], 0, ',', '.'); ?></td>
                                    <td style="color: #dc2626;">- Rp <?= number_format($h['potongan_diskon'], 0, ',', '.'); ?></td>
                                    <td style="font-weight: bold; color: #16a34a;">Rp <?= number_format($h['total_bayar_bersih'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($h['status_booking'] === 'Pending'): ?>
                                            <span class="badge badge-warning">Menunggu Validasi</span>
                                        <?php elseif ($h['status_booking'] === 'Paid'): ?>
                                            <span class="badge badge-success">Lunas (Paid)</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Dibatalkan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>