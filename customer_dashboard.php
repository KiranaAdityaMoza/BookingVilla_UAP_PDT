<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$id_customer = $_SESSION['id_customer'];
$total_transaksi = 0;

if (!empty($id_customer)) {
    try {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE id_customer = :id_customer");
        $stmtCount->execute(['id_customer' => $id_customer]);
        $total_transaksi = $stmtCount->fetchColumn();
    } catch (PDOException $e) {
        die("Gagal memuat data dashboard: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Customer - Villaku</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="customer-body">

    <?php include 'customer_navbar.php'; ?>

    <div class="luxury-container animate-fade-up" style="margin-top: 40px;">
        <div class="section-title-premium">
            <div class="title-heading-zone">
                <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama_user'] ?? ''); ?>!</h2>
            </div>
            <p>Kelola pesanan properti liburan Anda dengan praktis dan eksklusif.</p>
        </div>

        <div style="display: flex; gap: 30px; margin-top: 30px; align-items: flex-start;">
            <div class="card" style="flex: 1; padding: 30px; text-align: center; max-width: 300px;">
                <div style="font-size: 40px; margin-bottom: 10px;">💬</div>
                <h3 style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 10px;">Total Transaksi Kamu</h3>
                <span style="font-size: 70px; font-weight: 800; color: #0f172a; line-height: 1;"><?= $total_transaksi; ?></span>
            </div>

            <div class="card" style="flex: 2; padding: 30px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">✨ Petunjuk Pemesanan Properti</h3>
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="background: #f1f5f9; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; color: #0f172a; flex-shrink: 0;">01</div>
                    <div>
                        <h4 style="margin: 0 0 5px 0; color: #0f172a;">Eksplorasi Properti Cabang</h4>
                        <p style="margin: 0; color: #64748b; font-size: 14px;">Buka menu <b>Pesan Vila</b> untuk melihat galeri dan katalog seluruh properti resort aktif kami.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="background: #f1f5f9; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; color: #0f172a; flex-shrink: 0;">02</div>
                    <div>
                        <h4 style="margin: 0 0 5px 0; color: #0f172a;">Sistem Potongan Harga Otomatis</h4>
                        <p style="margin: 0; color: #64748b; font-size: 14px;">Sistem otomatis memberikan Diskon 10% melalui <i>Custom Function database</i> jika total nilai sewa Anda berada di atas nominal Rp 3.000.000.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div style="background: #f1f5f9; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; color: #0f172a; flex-shrink: 0;">03</div>
                    <div>
                        <h4 style="margin: 0 0 5px 0; color: #0f172a;">Validasi &amp; Verifikasi Finansial</h4>
                        <p style="margin: 0; color: #64748b; font-size: 14px;">Setelah melakukan reservasi awal, admin akan memverifikasi bukti pembayaran Anda agar status berubah menjadi konfirmasi penuh.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>