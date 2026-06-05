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
    <title>Dashboard Pelanggan - Villaku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="customer-body">

    <!-- Memanggil navbar bawaan sistem kamu -->
    <?php include 'customer_navbar.php'; ?>

    <div class="luxury-container animate-fade-up">
        
        <!-- BANNER WELCOME LUXURY RESORT THEME -->
        <div class="luxury-banner">
            <div class="banner-overlay-glass">
                <span class="banner-badge">PREMIUM ESCAPE</span>
                <h1>Selamat Datang Kembali,<br><span class="highlight-name"><?= htmlspecialchars($_SESSION['nama_user']); ?>!</span></h1>
                <p>Temukan kenyamanan menginap terbaik di klaster Puncak yang sejuk atau klaster Pantai kami yang eksotis.</p>
            </div>
        </div>

        <!-- LAYOUT GRID UTAMA -->
        <div class="grid-luxury-dashboard">
            
            <!-- SISI KIRI: KARTU TOTAL TRANSAKSI MEWAH -->
            <div class="card card-luxury-counter">
                <div class="card-accent-line"></div>
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="luxury-icon"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <h3 class="card-luxury-subtitle">Total Transaksi Kamu</h3>
                <p class="counter-luxury-number"><?= $total_booking; ?></p>
                <a href="customer_riwayat.php" class="btn-luxury-action">
                    <span>Lihat Riwayat</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="arrow-icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>

            <!-- SISI KANAN: KARTU PETUNJUK PEMESANAN -->
            <div class="card card-luxury-instructions">
                <div class="card-accent-line"></div>
                <h3 class="instruction-luxury-title">
                    <span class="icon-sparkle">✨</span> Petunjuk Pemesanan Properti
                </h3>
                
                <div class="instruction-step-wrapper">
                    <div class="step-item">
                        <div class="step-number">01</div>
                        <div class="step-text">
                            <h4>Eksplorasi Properti Cabang</h4>
                            <p>Buka menu <b>Pesan Vila</b> untuk melihat galeri dan katalog seluruh properti resort aktif kami.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">02</div>
                        <div class="step-text">
                            <h4>Sistem Potongan Harga Otomatis</h4>
                            <p>Sistem otomatis memberikan <span class="badge-discount">Diskon 10%</span> melalui <i>Custom Function database</i> jika total nilai sewa Anda berada di atas nominal Rp 3.000.000.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">03</div>
                        <div class="step-text">
                            <h4>Validasi &amp; Verifikasi Finansial</h4>
                            <p>Setelah mengajukan pemesanan, harap tunggu konfirmasi dari pihak Admin untuk memvalidasi bukti pembayaran Anda di sistem pusat.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>