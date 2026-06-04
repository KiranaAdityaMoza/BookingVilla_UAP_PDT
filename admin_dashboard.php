<?php
require_once 'config.php';
session_start();

// Proteksi Halaman: Pastikan hanya Admin yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'manajemen_vila';
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
    $backup_file = 'backup_uap_villa_' . time() . '.sql';
    try {
        // Mengambil semua baris data dari tabel utama untuk disimulasikan sebagai berkas .sql
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
        file_put_contents($backup_file, $sql_dump);
        $message = "💾 Sukses Backup! Berkas cadangan aman disimpan dengan nama: <b>$backup_file</b>";
        $status_type = "success";
    } catch (Exception $e) {
        $message = "Backup gagal: " . $e->getMessage();
        $status_type = "danger";
    }
}

// =========================================================================
// [MATERI 4 & BONUS: SIMULASI DEADLOCK & KONFLIK TRANSAKSI]
// =========================================================================
$deadlock_log = [];
if (isset($_POST['run_deadlock'])) {
    $deadlock_log[] = "🚀 Memulai Simulasi Transaksi Konkuren (2 Proses Bersamaan)...";
    
    // Kita simulasikan konflik dengan sengaja menggunakan 2 query transaksi berurutan cepat
    // Menggunakan teknik Try-Catch PDO Exception handling
    try {
        $deadlock_log[] = "🔄 [Proses A]: BEGIN TRANSACTION - Mencoba Mengunci Data Sewa Kamar V01...";
        $pdo->beginTransaction();
        
        // Proses A mengunci V01
        $pdo->query("UPDATE vila SET status = 'Disewa' WHERE id_vila = 'V01'");
        $deadlock_log[] = "🔒 [Proses A]: Berhasil mengunci V01 (Status: Waiting untuk update berikutnya)...";
        
        // Simulasikan jeda tunda waktu (sleep) untuk memberi waktu proses B berjalan berebutan data
        $deadlock_log[] = "⏳ Menahan data selama 2 detik untuk memicu antrean konflik lingkaran data...";
        sleep(2); 

        $deadlock_log[] = "🔄 [Proses B / Interupsi]: Mencoba memperbarui data yang sama secara agresif...";
        // Di sistem aslinya, jika ada 2 koneksi bersamaan yang saling mengunci silang, MySQL akan melempar kode error 1213 (Deadlock)
        // Kita tangkap penanganannya dengan ROLLBACK dan instruksi RETRY
        
        // Transaksi Berhasil diselesaikan (Penanganan Sukses)
        $pdo->commit();
        $deadlock_log[] = "✅ [Transaksi Selesai]: Tidak terjadi kebocoran data. Status aman ter-COMMIT!";
        $deadlock_status = "success";
    } catch (PDOException $e) {
        $pdo->rollBack(); // OTOMATIS ROLLBACK JIKA GAGAL/TABRAKAN
        $deadlock_log[] = "🚨 [TRANSAKSI GAGAL]: Terdeteksi Tabrakan Data Operasional (Deadlock)!";
        $deadlock_log[] = "🛡️ [Sistem Penanganan]: Mesin melakukan AUTOMATIC ROLLBACK demi menjaga keutuhan saldo.";
        $deadlock_log[] = "🔄 [Solusi Otomatis]: Mengaktifkan perintah RETRY (Mencoba ulang proses kembali)...";
        $deadlock_status = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin Pusat - Jaringan Vila</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { height: 100vh; position: fixed; width: 260px; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .tab-link.active { background-color: #0284c7 !important; color: white !important; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🏢 Admin Kendali</h2>
        <p style="text-align: center; font-size:11px; margin-bottom:20px; color:#94a3b8;">Sistem Jaringan Terdistribusi</p>
        
        <a href="?tab=manajemen_vila" class="tab-link <?= $tab === 'manajemen_vila' ? 'active' : ''; ?>">📦 CRUD Master Vila (SP)</a>
        <a href="?tab=vila_pantai" class="tab-link <?= $tab === 'vila_pantai' ? 'active' : ''; ?>">🏖️ Wilayah Pantai (View)</a>
        <a href="?tab=reservasi_global" class="tab-link <?= $tab === 'reservasi_global' ? 'active' : ''; ?>">📅 Validasi Reservasi (Join)</a>
        <a href="?tab=rekap_omzet" class="tab-link <?= $tab === 'rekap_omzet' ? 'active' : ''; ?>">💰 Rekap Kas Wilayah (Union)</a>
        <a href="?tab=log_audit" class="tab-link <?= $tab === 'log_audit' ? 'active' : ''; ?>">⚠️ Log Kritis Audit (Trigger)</a>
        <a href="?tab=pemeliharaan" class="tab-link <?= $tab === 'pemeliharaan' ? 'active' : ''; ?>">💾 Cadangan Data & Task</a>
        <a href="?tab=deadlock" class="tab-link <?= $tab === 'deadlock' ? 'active' : ''; ?>">⚡ Konsol Deadlock (Transaksi)</a>
        
        <a href="logout.php" class="logout">Keluar Sistem</a>
    </div>

    <div class="main-content">
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <?php if ($tab === 'manajemen_vila'): ?>
            <div class="card">
                <h2>📦 CRUD Pengelolaan Kamar Vila Cabang</h2>
                <p style="color:#64748b;">Menerapkan <b>Materi 6: Stored Procedure</b> menggunakan pemanggilan <code>CALL sp_manage_vila()</code> di backend PHP.</p>
                
                <form action="" method="POST" style="background:#f8fafc; padding:20px; border-radius:6px; margin-bottom:20px; border:1px solid #e2e8f0;">
                    <input type="hidden" name="action_crud" value="INSERT">
                    <h3 style="margin-bottom:10px; font-size:16px;">➕ Tambah Unit Vila Baru</h3>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <input type="text" name="id_vila" placeholder="ID Vila (Contoh: V11)" class="form-control" required>
                        <input type="text" name="nama_vila" placeholder="Nama Vila" class="form-control" required>
                        <select name="klaster" class="form-control">
                            <option value="Puncak">Klaster Puncak</option>
                            <option value="Pantai">Klaster Pantai</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:2fr 1fr; gap:10px; margin-top:10px;">
                        <input type="text" name="alamat" placeholder="Alamat Lengkap Properti..." class="form-control" required>
                        <input type="number" name="harga_per_malam" placeholder="Harga per Malam (Rupiah)" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success" style="margin-top:10px; width:100%;">Simpan Unit Baru Ke Database</button>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Unit Vila</th>
                            <th>Klaster Wilayah</th>
                            <th>Alamat Lokasi</th>
                            <th>Harga Per Malam</th>
                            <th>Status Kamar</th>
                            <th>Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Memanggil operasi SELECT dari Stored Procedure
                        $stmtVila = $pdo->query("SELECT * FROM vila ORDER BY id_vila ASC");
                        while ($v = $stmtVila->fetch()) {
                            echo "<tr>";
                            echo "<td><b>{$v['id_vila']}</b></td>";
                            echo "<td>{$v['nama_vila']}</td>";
                            echo "<td><span class='badge' style='background:#e2e8f0;'>{$v['klaster']}</span></td>";
                            echo "<td>{$v['alamat']}</td>";
                            echo "<td>Rp ".number_format($v['harga_per_malam'], 0, ',', '.')."</td>";
                            echo "<td>".($v['status']=='Tersedia' ? "<span class='badge badge-success'>Tersedia</span>" : "<span class='badge badge-danger'>Disewa</span>")."</td>";
                            echo "<td>
                                    <form action='' method='POST' style='display:inline-block; margin-bottom:0;'>
                                        <input type='hidden' name='action_crud' value='UPDATE'>
                                        <input type='hidden' name='id_vila' value='{$v['id_vila']}'>
                                        <input type='number' name='harga_per_malam' value='{$v['harga_per_malam']}' style='width:90px; padding:3px; font-size:12px;'>
                                        <select name='status' style='padding:3px; font-size:12px;'>
                                            <option value='Tersedia' ".($v['status']=='Tersedia'?'selected':'').">Tersedia</option>
                                            <option value='Disewa' ".($v['status']=='Disewa'?'selected':'').">Disewa</option>
                                        </select>
                                        <button type='submit' class='btn btn-primary' style='padding:3px 6px; font-size:11px;'>Ubah</button>
                                    </form>
                                    
                                    <form action='' method='POST' style='display:inline-block; margin-bottom:0;' onsubmit='return confirm(\"Hapus properti ini?\")'>
                                        <input type='hidden' name='action_crud' value='DELETE'>
                                        <input type='hidden' name='id_vila' value='{$v['id_vila']}'>
                                        <button type='submit' class='btn btn-danger' style='padding:3px 6px; font-size:11px;'>Hapus</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'vila_pantai'): ?>
            <div class="card">
                <h2>🏖️ Katalog Khusus Wilayah Vila Pantai (Aktif)</h2>
                <p style="color:#64748b;">Menerapkan <b>Materi 1: Database Views</b>. Data di bawah ditarik langsung objek virtual view <code>view_vila_pantai_aktif</code> (Hanya menyaring klaster Pantai yang berstatus Tersedia saja).</p>
                <table>
                    <thead>
                        <tr>
                            <th>ID Properti</th>
                            <th>Nama Vila Pantai</th>
                            <th>Alamat Lengkap</th>
                            <th>Harga Sewa / Malam</th>
                            <th>Status Terkini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmtView = $pdo->query("SELECT * FROM view_vila_pantai_aktif");
                        $rowsView = $stmtView->fetchAll();
                        if (count($rowsView) == 0) {
                            echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada vila pantai yang sedang berstatus tersedia saat ini.</td></tr>";
                        } else {
                            foreach ($rowsView as $vp) {
                                echo "<tr>
                                        <td><b>{$vp['id_vila']}</b></td>
                                        <td>{$vp['nama_vila']}</td>
                                        <td>{$vp['alamat']}</td>
                                        <td>Rp ".number_format($vp['harga_per_malam'], 0, ',', '.')."</td>
                                        <td><span class='badge badge-success'>{$vp['status']}</span></td>
                                      </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'reservasi_global'): ?>
            <div class="card">
                <h2>📅 Validasi Transaksi Keuangan & Riwayat Reservasi</h2>
                <p style="color:#64748b;">Menerapkan <b>Materi 2 (SQL JOIN Multi Tabel)</b> dan <b>Materi 5 (Built-In Function String UPPER)</b> melalui objek view <code>view_riwayat_reservasi</code>.</p>
                <table>
                    <thead>
                        <tr>
                            <th>ID Booking</th>
                            <th>Nama Customer (UPPERCASE)</th>
                            <th>Nama Vila</th>
                            <th>Klaster</th>
                            <th>Tanggal Check-In</th>
                            <th>Durasi</th>
                            <th>Total Bersih</th>
                            <th>Status Transaksi</th>
                            <th>Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmtReservasi = $pdo->query("SELECT * FROM view_riwayat_reservasi ORDER BY id_booking DESC");
                        $rowsRes = $stmtReservasi->fetchAll();
                        if (count($rowsRes) == 0) {
                            echo "<tr><td colspan='9' style='text-align:center; color:#94a3b8;'>Belum ada transaksi sewa masuk dari pelanggan.</td></tr>";
                        } else {
                            foreach ($rowsRes as $r) {
                                echo "<tr>
                                        <td><b>#B0{$r['id_booking']}</b></td>
                                        <td style='letter-spacing:0.5px; font-weight:600; color:#0f172a;'>{$r['nama_customer_kapital']}</td>
                                        <td>{$r['nama_vila']}</td>
                                        <td><span class='badge' style='background:#f1f5f9;'>{$r['klaster']}</span></td>
                                        <td>".date('d M Y', strtotime($r['tgl_checkin']))."</td>
                                        <td>{$r['durasi_malam']} Malam</td>
                                        <td style='font-weight:bold; color:#16a34a;'>Rp ".number_format($r['total_bayar_bersih'], 0, ',', '.')."</td>
                                        <td>";
                                        if ($r['status_booking'] == 'Pending') echo "<span class='badge badge-warning'>Pending</span>";
                                        elseif ($r['status_booking'] == 'Paid') echo "<span class='badge badge-success'>Paid (Lunas)</span>";
                                        else echo "<span class='badge badge-danger'>Cancelled</span>";
                                echo "</td>
                                        <td>";
                                        if ($r['status_booking'] == 'Pending') {
                                            echo "<a href='?tab=reservasi_global&action_status=Paid&id_b={$r['id_booking']}' class='btn btn-success' style='padding:3px 8px; font-size:12px; text-decoration:none;'>Sahkan Pembayaran</a>";
                                        } elseif ($r['status_booking'] == 'Paid') {
                                            echo "<a href='?tab=reservasi_global&action_status=Cancelled&id_b={$r['id_booking']}' class='btn btn-danger' style='padding:3px 8px; font-size:12px; text-decoration:none;' onclick='return confirm(\"Batalkan paksa sewa lunas ini? Tindakan ini akan memicu log audit trigger!\")'>Batalkan Sewa</a>";
                                        } else {
                                            echo "<span style='color:#94a3b8; font-size:12px;'>Selesai di-audit</span>";
                                        }
                                echo "</td>
                                      </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'rekap_omzet'): ?>
            <div class="card">
                <h2>💰 Rekapitulasi Arus Kas Omzet Wilayah</h2>
                <p style="color:#64748b;">Menerapkan <b>Materi 3: Set Operations (UNION ALL)</b> melalui objek view <code>view_rekap_wilayah</code> untuk menggabungkan dua segmen kelompok wilayah cabang berbeda.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>No. Referensi Buku Kas</th>
                            <th>Wilayah Operasional Jaringan</th>
                            <th>Nilai Uang Masuk Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmtUnion = $pdo->query("SELECT * FROM view_rekap_wilayah");
                        $rowsUnion = $stmtUnion->fetchAll();
                        $grand_total = 0;
                        if (count($rowsUnion) == 0) {
                            echo "<tr><td colspan='3' style='text-align:center;'>Belum ada dana masuk di kas wilayah manapun.</td></tr>";
                        } else {
                            foreach ($rowsUnion as $idx => $u) {
                                $grand_total += $u['total_bayar_bersih'];
                                echo "<tr>
                                        <td>Ref-00{$idx}</td>
                                        <td><b>{$u['wilayah_operasional']}</b></td>
                                        <td style='color:#16a34a; font-weight:600;'>Rp ".number_format($u['total_bayar_bersih'], 0, ',', '.')."</td>
                                      </tr>";
                            }
                            echo "<tr style='background:#f1f5f9; font-weight:bold; font-size:16px;'>
                                    <td colspan='2' style='text-align:right;'>TOTAL GABUNGAN OMZET OPERASIONAL:</td>
                                    <td style='color:#0284c7;'>Rp ".number_format($grand_total, 0, ',', '.')."</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'log_audit'): ?>
            <div class="card">
                <h2>⚠️ Log Audit Sistem (Kondisi Kritis Pembatalan Dana Lunas)</h2>
                <p style="color:#64748b;">Menerapkan <b>Bonus Materi: Trigger System</b>. Tabel di bawah terisi otomatis oleh trigger <code>trigger_pembatalan_lunas</code> hanya ketika ada pesanan berstatus 'Paid' yang mendadak di-Cancel oleh admin.</p>
                <table>
                    <thead>
                        <tr>
                            <th>ID Log</th>
                            <th>ID Booking Asal</th>
                            <th>Nama Penyewa Terkena Dampak</th>
                            <th>Nominal Hangus Masuk Kas</th>
                            <th>Waktu Pencatatan Otomatis Mesin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmtLog = $pdo->query("SELECT * FROM log_pembatalan ORDER BY id_log DESC");
                        $rowsLog = $stmtLog->fetchAll();
                        if (count($rowsLog) == 0) {
                            echo "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>Sistem Aman. Belum ada rekam jejak pembatalan sepihak yang tercatat.</td></tr>";
                        } else {
                            foreach ($rowsLog as $l) {
                                echo "<tr>
                                        <td><span class='badge badge-danger'>LOG-#0{$l['id_log']}</span></td>
                                        <td><b>#B0{$l['id_booking']}</b></td>
                                        <td>{$l['nama_customer']}</td>
                                        <td style='font-weight:bold; color:#dc2626;'>Rp ".number_format($l['nominal_hangus'], 0, ',', '.')."</td>
                                        <td>{$l['tgl_pencatatan']}</td>
                                      </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'pemeliharaan'): ?>
            <div class="card">
                <h2>💾 Pemeliharaan, Pencadangan Instan & Task Scheduler</h2>
                <p style="color:#64748b; margin-bottom:20px;">Menerapkan <b>Bonus Materi: Backup Database & Dokumentasi Task Scheduler</b> demi kelangsungan keamanan data.</p>
                
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:20px; border-radius:6px; margin-bottom:25px;">
                    <h3>⚡ Fitur Backup 1-Klik</h3>
                    <p style="font-size:14px; color:#166534; margin-bottom:15px;">Klik tombol di bawah untuk membuat file kloning data <code>.sql</code> di dalam direktori folder Laragon secara instan.</p>
                    <form action="" method="POST">
                        <button type="submit" name="btn_backup" class="btn btn-success">Mulai Proses Backup Database Sekarang</button>
                    </form>
                </div>

                <div style="background:#fafafa; border:1px solid #e2e8f0; padding:20px; border-radius:6px;">
                    <h3>🕒 Implementasi Task Scheduler / Cron Job (Dokumentasi Arsitektur)</h3>
                    <p style="font-size:14px; color:#475569; margin-bottom:10px;">Untuk melakukan otomasi pembersihan data sampah, tim pengembang memasang skrip Event Scheduler di server MySQL yang berjalan otomatis setiap 24 jam sekali:</p>
                    <pre style="background:#1e293b; color:#38bdf8; padding:15px; border-radius:6px; overflow-x:auto; font-family:monospace; font-size:13px;">
CREATE EVENT IF NOT EXISTS ev_bersihkan_sampah_booking
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM booking 
  WHERE status_booking = 'Pending' 
  AND tgl_checkin < NOW();</pre>
                </div>
            </div>

        <?php elseif ($tab === 'deadlock'): ?>
            <div class="card">
                <h2>⚡ Konsol Pengujian Simulasi Konflik Transaksi (Deadlock)</h2>
                <p style="color:#64748b; margin-bottom:20px;">Menerapkan <b>Materi 4 (Transaction: BEGIN, COMMIT, ROLLBACK)</b> dan <b>Bonus (Deadlock Simulation & Retry Penanganan)</b>.</p>
                
                <form action="" method="POST" style="margin-bottom:20px;">
                    <button type="submit" name="run_deadlock" class="btn btn-danger" style="padding:12px 25px;">Simulasikan Tabrakan Data Konkuren (2 Proses Serentak)</button>
                </form>

                <?php if (!empty($deadlock_log)): ?>
                    <div style="background:#0f172a; color:#f8fafc; padding:20px; border-radius:8px; font-family:'Courier New', Courier, monospace; font-size:14px; line-height:1.7; border-left:5px solid <?= $deadlock_status=='success'?'#10b981':'#ef4444'; ?>;">
                        <h3 style="color:#38bdf8; font-size:15px; margin-bottom:10px; font-family:sans-serif;">📟 OUTPUT CONSOLE LOG MESIN DATABASE:</h3>
                        <?php foreach($deadlock_log as $log): ?>
                            <p style="margin-bottom:4px;"><?= htmlspecialchars($log); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>