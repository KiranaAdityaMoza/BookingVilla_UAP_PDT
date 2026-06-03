<?php
// Pastikan session sudah dimulai di file utama sebelum memanggil navbar ini
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    body { display: block; background-color: #f8fafc; }
    .navbar { background-color: #0f172a; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .nav-links { display: flex; gap: 15px; }
    .nav-links a { color: #cbd5e1; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 500; transition: 0.3s; }
    .nav-links a:hover, .nav-links a.active { background-color: #334155; color: white; }
    .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
</style>

<div class="navbar">
    <div style="display: flex; align-items: center; gap: 30px;">
        <h2 style="margin-bottom: 0;">🏨 Portal Vila</h2>
        <div class="nav-links">
            <a href="customer_dashboard.php" class="<?= $current_page == 'customer_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            <a href="customer_villa.php" class="<?= $current_page == 'customer_villa.php' ? 'active' : ''; ?>">Pesan Vila</a>
            <a href="customer_riwayat.php" class="<?= $current_page == 'customer_riwayat.php' ? 'active' : ''; ?>">Riwayat Pemesanan</a>
        </div>
    </div>
    <div>
        <span style="margin-right: 20px; font-size: 14px;">Halo, <b><?= htmlspecialchars($_SESSION['nama_user']); ?></b></span>
        <a href="logout.php" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px; text-decoration: none;">Keluar</a>
    </div>
</div>