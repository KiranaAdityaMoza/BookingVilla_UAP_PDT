<?php
require_once 'config.php';
session_start();

// Proteksi Halaman: Pastikan hanya Admin yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'manajemen_vila';
$filter = $_GET['filter'] ?? 'Semua';
$message = '';
$status_type = '';

// =========================================================================
// [MATERI 6: STORED PROCEDURE] AKSI UTK FORM CRUD MANAJEMEN VILA
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_crud'])) {
    $action = $_POST['action_crud'];
    $id      = isset($_POST['id_vila']) ? trim($_POST['id_vila']) : '';
    $nama    = isset($_POST['nama_vila']) ? trim($_POST['nama_vila']) : '';
    $alamat  = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $klaster = isset($_POST['klaster']) ? $_POST['klaster'] : 'Puncak';
    $harga   = isset($_POST['harga_per_malam']) ? floatval($_POST['harga_per_malam']) : 0;
    $status  = isset($_POST['status']) ? $_POST['status'] : 'Tersedia';

    try {
        if ($action === 'INSERT') {
            $stmt = $pdo->prepare("CALL sp_manage_vila('INSERT', :id, :nama, :alamat, :klaster, :harga, :status)");
            $stmt->execute(['id'=>$id, 'nama'=>$nama, 'alamat'=>$alamat, 'klaster'=>$klaster, 'harga'=>$harga, 'status'=>$status]);
            $message = "🟢 Berhasil Menambah Vila Baru via Stored Procedure!";
            $status_type = "success";
        } elseif ($action === 'UPDATE') {
            $stmt = $pdo->prepare("CALL sp_manage_vila('UPDATE', :id, '', '', '', :harga, :status)");
            $stmt->execute(['id'=>$id, 'harga'=>$harga, 'status'=>$status]);
            $message = "🔵 Berhasil Memperbarui Properti via Stored Procedure!";
            $status_type = "success";
        } elseif ($action === 'DELETE') {
            $stmt = $pdo->prepare("CALL sp_manage_vila('DELETE', :id, '', '', '', 0, '')");
            $stmt->execute(['id'=>$id]);
            $message = "🔴 Berhasil Menghapus Vila via Stored Procedure!";
            $status_type = "success";
        }
    } catch (PDOException $e) {
        $message = "Gagal mengeksekusi Prosedur: " . $e->getMessage();
        $status_type = "danger";
    }
}

// =========================================================================
// PROSES VALIDASI & PEMBATALAN (UNTUK MEMICU REKAMAN OTOMATIS TRIGGER)
// =========================================================================
if (isset($_GET['action_status']) && isset($_GET['id_b'])) {
    $act_status = $_GET['action_status'];
    $id_b       = intval($_GET['id_b']);

    try {
        if ($act_status === 'Paid') {
            // Validasi Pembayaran biasa & Ubah status vila jadi disewa
            $pdo->beginTransaction();
            $stmt1 = $pdo->prepare("UPDATE booking SET status_booking = 'Paid' WHERE id_booking = :id");
            $stmt1->execute(['id' => $id_b]);
            
            $stmtGetVila = $pdo->prepare("SELECT id_vila FROM booking WHERE id_booking = :id");
            $stmtGetVila->execute(['id' => $id_b]);
            $vId = $stmtGetVila->fetchColumn();
            
            $stmt2 = $pdo->prepare("UPDATE vila SET status = 'Disewa' WHERE id_vila = :vid");
            $stmt2->execute(['vid' => $vId]);
            $pdo->commit();
            
            $message = "Pembayaran Berhasil Divalidasi!";
            $status_type = "success";
        } elseif ($act_status === 'Cancelled') {
            // [BONUS MATERI: TRIGGER] Memicu trigger_pembatalan_lunas
            // Jika status awalnya 'Paid' diubah jadi 'Cancelled', trigger otomatis mencatat ke log_pembatalan
            $pdo->beginTransaction();
            $stmtGetVila = $pdo->prepare("SELECT id_vila FROM booking WHERE id_booking = :id");
            $stmtGetVila->execute(['id' => $id_b]);
            $vId = $stmtGetVila->fetchColumn();

            $stmt1 = $pdo->prepare("UPDATE booking SET status_booking = 'Cancelled' WHERE id_booking = :id");
            $stmt1->execute(['id' => $id_b]);
            
            $stmt2 = $pdo->prepare("UPDATE vila SET status = 'Tersedia' WHERE id_vila = :vid");
            $stmt2->execute(['vid' => $vId]);
            $pdo->commit();

            $message = "Booking Dibatalkan Sepihak! Audit Log Otomatis Terbentuk via Trigger.";
            $status_type = "warning";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Gagal mengubah status: " . $e->getMessage();
        $status_type = "danger";
    }
}

// =========================================================================
// [BONUS MATERI: BACKUP DATABASE INSTAN]
// =========================================================================
if (isset($_POST['btn_backup'])) {
    $folder_target = __DIR__ . '/backup/';
    
    if (!is_dir($folder_target)) {
        mkdir($folder_target, 0755, true);
    }

    $backup_file = 'backup_uap_villa_' . time() . '.sql';
    $path_lengkap = $folder_target . $backup_file; 

    try {
        $tables = ['customer', 'users', 'vila', 'booking', 'log_pembatalan'];
        $sql_dump = "-- CADANGAN DATABASE UAP VILLA \n-- Dibuat otomatis: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $t) {
            $rows = $pdo->query("SELECT * FROM $t")->fetchAll();
            $sql_dump .= "-- Data Tabel $t \n";
            foreach ($rows as $r) {
                $values = array_map(function($v) use ($pdo) { return $v === null ? 'NULL' : $pdo->quote($v); }, $r);
                $sql_dump .= "INSERT INTO $t VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql_dump .= "\n";
        }
        
        file_put_contents($path_lengkap, $sql_dump);
        
        // GUNAKAN SESSION BUKAN VARIABEL BIASA BIAR TIDAK HILANG SAAT REDIRECT
        $_SESSION['msg'] = "💾 Sukses Backup! Berkas cadangan aman terkumpul di folder <b>backup/$backup_file</b>";
        $_SESSION['msg_type'] = "success";
        
        // REDIRECT KEMBALI KE TAB PEMELIHARAAN UNTUK MENGHILANGKAN POST DATA
        header("Location: admin_dashboard.php?tab=pemeliharaan");
        exit;
    } catch (Exception $e) {
        $_SESSION['msg'] = "Backup gagal: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: admin_dashboard.php?tab=pemeliharaan");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Villaku Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="sidebar">
        <h2>🏡 Villaku</h2>
        <p class="sidebar-subtitle">Admin Panel</p>
        <hr class="sidebar-divider">
        
        <a href="?tab=manajemen_vila" class="tab-link <?= $tab=='manajemen_vila'?'active':'' ?>">Master Vila</a>
        <a href="?tab=vila_pantai" class="tab-link <?= $tab=='vila_pantai'?'active':'' ?>">Katalog Vila</a>
        <a href="?tab=reservasi_global" class="tab-link <?= $tab=='reservasi_global'?'active':'' ?>">Validasi Reservasi</a>
        <a href="?tab=rekap_omzet" class="tab-link <?= $tab=='rekap_omzet'?'active':'' ?>">Rekap Kas</a>
        <a href="?tab=log_audit" class="tab-link <?= $tab=='log_audit'?'active':'' ?>">Log Audit</a>
        <a href="?tab=pemeliharaan" class="tab-link <?= $tab=='pemeliharaan'?'active':'' ?>">Backup</a>
        
        <a href="admin_deadlock.php" class="tab-link">Deadlock</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="main-content">
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <div class="welcome-banner">
            <div class="banner-overlay-glass">
                <h1>Dashboard Admin Villaku</h1>
                <p>Sistem pengawasan terintegrasi lengkap dengan manajemen filter data & showroom galeri unit properti.</p>
            </div>
        </div>

        <?php if($tab=='manajemen_vila'){ ?>
            <div class="card">
                <h3 class="card-title">➕ Tambah Unit Vila Baru</h3>
                
                <form method="POST" action="" class="admin-form">
                    <input type="hidden" name="action_crud" value="INSERT">
                    
                    <div class="grid-3">
                        <div class="form-group">
                            <label>ID Vila</label>
                            <input name="id_vila" placeholder="ID Villa (Contoh: V11)" class="form-control" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Nama Unit Vila</label>
                            <input name="nama_vila" placeholder="Nama Villa" class="form-control" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Klaster Wilayah Cabang</label>
                            <select name="klaster" class="form-control">
                                <option value="Pantai">Pantai</option>
                                <option value="Puncak">Puncak</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Alamat Lengkap Properti</label>
                            <input name="alamat" placeholder="Alamat lengkap lokasi unit..." class="form-control" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Harga Sewa per Malam (Rupiah)</label>
                            <input name="harga_per_malam" placeholder="Contoh: 750000" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">Tambah Unit Baru</button>
                </form>
            </div>

            <div class="table-wrapper animate-fade-up">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nama Unit Vila</th>
                            <th>Klaster Wilayah</th>
                            <th>Harga Per Malam</th>
                            <th style="text-align: center; width: 150px;">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q=$pdo->query("SELECT * FROM vila");
                        foreach($q as $v){
                            $badge_class = (strtolower($v['klaster']) == 'pantai') ? 'badge-success' : 'badge-warning';
                        ?>
                        <tr>
                            <td><b><?= $v['id_vila'] ?></b></td>
                            <td><span class="villa-name"><?= htmlspecialchars($v['nama_vila']) ?></span></td>
                            <td><span class="badge <?= $badge_class ?>">Klaster <?= $v['klaster'] ?></span></td>
                            <td><strong class="price-text">Rp <?= number_format($v['harga_per_malam'], 0, ',', '.') ?></strong></td>
                            <td>
                                <div class="action-container" style="justify-content: center;">
                                    <form method="POST" action="" onsubmit="return confirm('Hapus properti ini?')">
                                        <input type="hidden" name="action_crud" value="DELETE">
                                        <input type="hidden" name="id_vila" value="<?= $v['id_vila'] ?>">
                                        <button type="submit" class="btn-table-inline btn-inline-hapus">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <?php if($tab=='vila_pantai'){ ?>
            <div class="card">
                <h3 class="card-title">🗺️ Galeri Showroom & Katalog Unit</h3>
                
                <form method="GET" action="" class="filter-form-inline">
                    <input type="hidden" name="tab" value="vila_pantai">
                    <label for="filter">Saring Klaster Wilayah:</label>
                    <select name="filter" id="filter" class="form-control filter-select">
                        <option value="Semua" <?= $filter=='Semua'?'selected':'' ?>>Semua Wilayah</option>
                        <option value="Pantai" <?= $filter=='Pantai'?'selected':'' ?>>Klaster Pantai</option>
                        <option value="Puncak" <?= $filter=='Puncak'?'selected':'' ?>>Klaster Puncak</option>
                    </select>
                    <button type="submit" class="btn-primary">Terapkan Filter</button>
                </form>
            </div>

            <div class="grid-3 animate-fade-up">
                <?php
                $sql="SELECT * FROM vila";
                if($filter!='Semua'){
                    $sql.=" WHERE klaster='$filter'";
                }
                $q=$pdo->query($sql);

                foreach($q as $v){
                    $img = "https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=600&auto=format&fit=crop";
                    if(strtolower($v['klaster']) == 'puncak') {
                        $img = "https://images.unsplash.com/photo-1580587771525-78b9dba3b914?q=80&w=600&auto=format&fit=crop";
                    }
                    $badge_class = (strtolower($v['klaster']) == 'pantai') ? 'badge-success' : 'badge-warning';
                ?>
                <div class="villa-card">
                    <img src="<?= $img ?>" onerror="this.src='https://via.placeholder.com/600x400?text=Villa'">
                    <div class="villa-card-content">
                        <h3><?= htmlspecialchars($v['nama_vila']) ?></h3>
                        <p class="text-muted-address"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($v['alamat']) ?></p>
                        <div class="mb-2 mt-1">
                            <span class="badge <?= $badge_class ?>">Klaster <?= $v['klaster'] ?></span>
                        </div>
                        <div class="villa-card-price">Rp <?= number_format($v['harga_per_malam'], 0, ',', '.') ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if($tab=='reservasi_global'){ ?>
            <div class="section-title">
                <h2>📅 Validasi Riwayat Reservasi Masuk</h2>
                <p>Data administrasi real-time ditarik dari objek database view <code>view_riwayat_reservasi</code>.</p>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Booking</th>
                            <th>Nama Customer</th>
                            <th>Unit Vila Pilihan</th>
                            <th style="text-align: center;">Status Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q=$pdo->query("SELECT * FROM view_riwayat_reservasi");
                        foreach($q as $r){
                        ?>
                        <tr>
                            <td><b>#B0<?= $r['id_booking'] ?></b></td>
                            <td><span class="villa-name"><?= htmlspecialchars($r['nama_customer_kapital']) ?></span></td>
                            <td><?= htmlspecialchars($r['nama_vila']) ?></td>
                            <td style="text-align: center;">
                                <?php if($r['status_booking'] == 'Paid'): ?>
                                    <span class="badge badge-success">Lunas (Paid)</span>
                                <?php elseif($r['status_booking'] == 'Pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <?php if($tab=='rekap_omzet'){ ?>
            <div class="section-title">
                <h2>💰 Rekapitulasi Pembukuan Kas Operasional</h2>
                <p>Penggabungan laporan finansial dua wilayah menggunakan klausa SQL <code>UNION ALL</code> via view <code>view_rekap_wilayah</code>.</p>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Wilayah Operasional</th>
                            <th>Total Dana Masuk Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q=$pdo->query("SELECT * FROM view_rekap_wilayah");
                        foreach($q as $r){
                        ?>
                        <tr>
                            <td><span class="villa-name"><b><?= htmlspecialchars($r['wilayah_operasional']) ?></b></span></td>
                            <td><strong class="price-text" style="color: var(--success);">Rp <?= number_format($r['total_bayar_bersih'], 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <?php if($tab=='log_audit'){ ?>
            <div class="section-title">
                <h2>⚠️ Rekam Jejak Audit Sistem Operasional</h2>
                <p>Log pengawasan otomatis terisi melalui pemicu sistem database <code>Trigger</code> saat terjadi pembatalan sepihak.</p>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 120px;">ID Log</th>
                            <th>ID Referensi Booking</th>
                            <th>Nominal Hangus Masuk Kas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q=$pdo->query("SELECT * FROM log_pembatalan");
                        foreach($q as $l){
                        ?>
                        <tr>
                            <td><span class="badge badge-danger">LOG-#0<?= $l['id_log'] ?></span></td>
                            <td><b>#B0<?= $l['id_booking'] ?></b></td>
                            <td><strong class="price-text" style="color: var(--danger-text);">Rp <?= number_format($l['nominal_hangus'], 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <?php if($tab=='pemeliharaan'){ ?>
            <div class="section-title">
                <h2>🗄️ Manajemen Pemeliharaan & Pencadangan Data</h2>
                <p>Gunakan utilitas panel ini untuk mengamankan data transaksi berkala ke berkas arsip terenkripsi.</p>
            </div>
            
            <div class="grid-2">
                <div class="card">
                    <h3 style="margin-bottom: 10px;">👨‍💻 Metode 1: Backup Manual</h3>
                    <p style="font-size: 13px; margin-bottom: 20px;">Membuat salinan database cadangan instan langsung via native PHP script system.</p>
                    <form method="POST" action="admin_backup.php">
                        <button type="submit" name="btn_backup" class="btn-primary" style="width: 100%;">Jalankan Backup Manual</button>
                    </form>
                </div>
        <?php } ?>

    </div>

</body>
</html>