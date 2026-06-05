<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$id_customer = $_SESSION['id_customer'];

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
    <title>Riwayat Pemesanan - Villaku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="customer-body">

    <?php include 'customer_navbar.php'; ?>

    <div class="luxury-container animate-fade-up">
        
        <div class="section-title-premium">
            <div class="title-heading-zone">
                <span class="title-icon">📅</span>
                <h2>Riwayat Pemesanan Kamar Kamu</h2>
            </div>
            <p>Menerapkan <b>Materi 2: SQL JOIN</b> antara tabel booking dan tabel master vila di database secara terdistribusi.</p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 110px;">ID Booking</th>
                        <th>Nama Properti Vila</th>
                        <th>Klaster Wilayah</th>
                        <th>Tanggal Check-In</th>
                        <th>Durasi</th>
                        <th>Harga Bruto</th>
                        <th>Potongan Diskon</th>
                        <th>Total Bayar (Net)</th>
                        <th style="text-align: center; width: 160px;">Status Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($historyData) === 0): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">Kamu belum pernah melakukan pemesanan vila.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historyData as $h): 
                            $badge_klaster = (strtolower($h['klaster']) == 'pantai') ? 'badge-success' : 'badge-warning';
                        ?>
                            <tr>
                                <td><b>#B0<?= $h['id_booking']; ?></b></td>
                                <td><span class="villa-name"><?= htmlspecialchars($h['nama_vila']); ?></span></td>
                                <td><span class="badge <?= $badge_klaster ?>">Klaster <?= $h['klaster']; ?></span></td>
                                <td><?= date('d M Y', strtotime($h['tgl_checkin'])); ?></td>
                                <td><?= $h['durasi_malam']; ?> Malam</td>
                                <td><span class="price-text" style="font-weight: 500;">Rp <?= number_format($h['total_harga_asli'], 0, ',', '.'); ?></span></td>
                                <td><span style="color: var(--danger-text); font-weight: 500;">- Rp <?= number_format($h['potongan_diskon'], 0, ',', '.'); ?></span></td>
                                <td><strong class="price-text" style="color: var(--success); font-size: 15px;">Rp <?= number_format($h['total_bayar_bersih'], 0, ',', '.'); ?></strong></td>
                                <td style="text-align: center;">
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

</body>
</html>