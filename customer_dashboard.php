<?php
require_once 'config.php';
session_start();

// Proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

// Ambil statistik ringkas untuk pajangan dashboard
$id_customer = $_SESSION['id_customer'];
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE id_customer = :id");
$stmtCount->execute(['id' => $id_customer]);
$total_booking = $stmtCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelanggan - Jaringan Vila</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'customer_navbar.php'; ?>

    <div class="container">
        <div class="card" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; padding: 40px; border-radius: 12px;">
            <h1 style="margin-bottom: 10px;">Selamat Datang Kembali, <?= htmlspecialchars($_SESSION['nama_user']); ?>!</h1>
            <p style="font-size: 16px; opacity: 0.9;">Temukan kenyamanan menginap terbaik di klaster Puncak yang sejuk atau klaster Pantai kami yang eksotis.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
            <div class="card" style="text-align: center; padding: 30px;">
                <h3 style="color: #64748b; font-size: 14px; text-transform: uppercase;">Total Transaksi Kamu</h3>
                <p style="font-size: 48px; font-weight: bold; color: #0f172a; margin: 15px 0;"><?= $total_booking; ?></p>
                <a href="customer_riwayat.php" class="btn btn-primary" style="display: inline-block; text-decoration: none; font-size: 13px;">Lihat Riwayat</a>
            </div>

            <div class="card" style="padding: 30px;">
                <h3>💡 Petunjuk Pemesanan</h3>
                <ul style="margin-left: 20px; color: #475569; line-height: 1.8;">
                    <li>Buka menu <b>Pesan Vila</b> untuk melihat katalog properti aktif kami.</li>
                    <li>Sistem otomatis memberikan <b>Diskon 10%</b> melalui <i>Custom Function database</i> jika total sewa kamu di atas Rp 3.000.000.</li>
                    <li>Setelah memesan, harap tunggu Admin memvalidasi pembayaran kamu di sistem pusat.</li>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>