<?php
// Pastikan session sudah dimulai di file utama sebelum memanggil navbar ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="luxury-navbar">
    <div class="nav-brand-zone">
        <h2><span>🏡</span> Villaku</h2>
    </div>
    
    <div class="nav-links-zone">
        <a href="customer_dashboard.php" class="nav-item <?= $current_page == 'customer_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="customer_villa.php" class="nav-item <?= $current_page == 'customer_villa.php' ? 'active' : ''; ?>">Pesan Vila</a>
        <a href="customer_riwayat.php" class="nav-item <?= $current_page == 'customer_riwayat.php' ? 'active' : ''; ?>">Riwayat Pemesanan</a>
    </div>
    
    <div class="nav-auth-zone">
        <span class="user-profile-greet">Halo, <b><?= htmlspecialchars($_SESSION['nama_user']); ?></b></span>
        <a href="logout.php" class="btn-logout-premium">Keluar</a>
    </div>
</div>