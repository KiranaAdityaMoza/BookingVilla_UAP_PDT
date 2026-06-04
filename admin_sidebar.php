<div class="sidebar">
    <h2>🏢 Admin Kendali</h2>
    <p style="text-align: center; font-size:11px; margin-bottom:20px; color:#94a3b8;">Sistem Jaringan Terdistribusi</p>
    
    <a href="admin_vila.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_vila.php' ? 'active' : ''; ?>">📦 CRUD Master Vila (SP)</a>
    <a href="admin_pantai.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_pantai.php' ? 'active' : ''; ?>">🏖️ Wilayah Pantai (View)</a>
    <a href="admin_reservasi.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_reservasi.php' ? 'active' : ''; ?>">📅 Validasi Reservasi (Join)</a>
    <a href="admin_omzet.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_omzet.php' ? 'active' : ''; ?>">💰 Rekap Kas Wilayah (Union)</a>
    <a href="admin_audit.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_audit.php' ? 'active' : ''; ?>">⚠️ Log Kritis Audit (Trigger)</a>
    <a href="admin_backup.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_backup.php' ? 'active' : ''; ?>">💾 Cadangan Data & Task</a>
    <a href="admin_deadlock.php" class="tab-link <?= basename($_SERVER['PHP_SELF']) === 'admin_deadlock.php' ? 'active' : ''; ?>">⚡ Konsol Deadlock (Transaksi)</a>
    
    <a href="logout.php" class="logout">Keluar Sistem</a>
</div>