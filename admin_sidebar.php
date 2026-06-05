<div class="sidebar">
    <h2>Admin Kendali</h2>
    <a href="admin_dashboard.php?tab=manajemen_vila" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' && (!isset($_GET['tab']) || $_GET['tab']=='manajemen_vila') ? 'active' : ''; ?>">Master Vila</a>
    <a href="admin_reservasi.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_reservasi.php' ? 'active' : ''; ?>">Validasi Reservasi</a>
    <a href="logout.php" class="logout">Keluar Sistem</a>
</div>