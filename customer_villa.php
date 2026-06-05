<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$id_user     = $_SESSION['id_user'];
$id_customer = $_SESSION['id_customer']; 
$message     = '';
$status_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_booking'])) {
    $id_vila     = $_POST['id_vila'];
    $tgl_checkin = $_POST['tgl_checkin'];
    $durasi      = intval($_POST['durasi_malam']);

    if (!empty($id_vila) && !empty($tgl_checkin) && $durasi > 0) {
        try {
            if (empty($id_customer)) {
                $nama_sementara = $_SESSION['username'];
                
                $stmtNewCust = $pdo->prepare("INSERT INTO customer (nama) VALUES (:nama)");
                $stmtNewCust->execute(['nama' => $nama_sementara]);
                
                $id_customer = $pdo->lastInsertId();

                $stmtUpdateUser = $pdo->prepare("UPDATE users SET id_customer_fk = :id_cust WHERE id_user = :id_user");
                $stmtUpdateUser->execute([
                    'id_cust' => $id_customer,
                    'id_user' => $id_user
                ]);

                $_SESSION['id_customer'] = $id_customer;
                $_SESSION['nama_user']   = $nama_sementara;
            }

            $checkin_baru  = date('Y-m-d', strtotime($tgl_checkin));
            $checkout_baru = date('Y-m-d', strtotime($tgl_checkin . " + " . $durasi . " days")); 
            
            $queryCheck = "SELECT COUNT(*) FROM booking 
                           WHERE id_vila = :id_vila 
                           AND status_booking IN ('Pending', 'Paid')
                           AND (
                               :checkin_baru < DATE_ADD(tgl_checkin, INTERVAL durasi_malam DAY)
                               AND 
                               :checkout_baru > tgl_checkin
                           )";
            
            $stmtCheck = $pdo->prepare($queryCheck);
            $stmtCheck->execute([
                'id_vila'       => $id_vila,
                'checkin_baru'  => $checkin_baru,
                'checkout_baru' => $checkout_baru
            ]);
            
            $is_bentrok = $stmtCheck->fetchColumn();

            if ($is_bentrok > 0) {
                $message = "❌ Maaf, Vila ini sudah dipesan pada rentang tanggal tersebut. Silakan tentukan tanggal berkunjung yang lain!";
                $status_type = "danger";
            } else {
                $stmtVila = $pdo->prepare("SELECT harga_per_malam FROM vila WHERE id_vila = :id");
                $stmtVila->execute(['id' => $id_vila]);
                $vila = $stmtVila->fetch();

                if ($vila) {
                    $harga_per_malam = $vila['harga_per_malam'];
                    $total_harga_asli = $harga_per_malam * $durasi;

                    $stmtDiskon = $pdo->prepare("SELECT GetDiskonVila(:total) AS nominal_potongan");
                    $stmtDiskon->execute(['total' => $total_harga_asli]);
                    $diskonData = $stmtDiskon->fetch();
                    
                    $potongan_diskon   = $diskonData['nominal_potongan'];
                    $total_bayar_bersih = $total_harga_asli - $potongan_diskon;

                    $queryInsert = "INSERT INTO booking (id_customer, id_vila, tgl_checkin, durasi_malam, total_harga_asli, potongan_diskon, total_bayar_bersih, status_booking) 
                                    VALUES (:id_cust, :id_v, :tgl, :durasi, :asli, :potongan, :bersih, 'Pending')";
                    
                    $stmtInsert = $pdo->prepare($queryInsert);
                    $stmtInsert->execute([
                        'id_cust'   => $id_customer,
                        'id_v'      => $id_vila,
                        'tgl'       => $checkin_baru, 
                        'durasi'    => $durasi,
                        'asli'      => $total_harga_asli,
                        'potongan'  => $potongan_diskon,
                        'bersih'    => $total_bayar_bersih
                    ]);

                    $message = "🎉 Reservasi berhasil dibuat! Silakan cek menu 'Riwayat Pemesanan' untuk melihat status validasi.";
                    $status_type = "success";
                }
            }
        } catch (PDOException $e) {
            $message = "Gagal memproses arsitektur transaksi: " . $e->getMessage();
            $status_type = "danger";
        }
    } else {
        $message = "Semua form input wajib diisi dengan benar!";
        $status_type = "danger";
    }
}

$villas = $pdo->query("SELECT * FROM vila ORDER BY klaster ASC, id_vila ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Vila - Villaku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="customer-body">

    <?php include 'customer_navbar.php'; ?>

    <div class="luxury-container animate-fade-up">
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <div class="section-title-premium">
            <div class="title-heading-zone">
                <span class="title-icon">🌟</span>
                <h2>Pilihan Properti Vila</h2>
            </div>
            <p>Sistem pintar otomatis mendeteksi ketersediaan jadwal secara <i>real-time</i> berdasarkan kalender pesanan lunas.</p>
        </div>
        
        <div class="grid-luxury-catalog">
            <?php foreach ($villas as $v): 
                $badge_class = (strtolower($v['klaster']) == 'pantai') ? 'badge-success' : 'badge-warning';
                $is_tersedia = (strtolower($v['status']) == 'tersedia');
            ?>
                <div class="card villa-booking-card" style="opacity: <?= $is_tersedia ? '1' : '0.85' ?>;">
                    <div class="card-accent-line"></div>
                    
                    <div class="villa-booking-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <span class="badge <?= $badge_class ?>">Klaster <?= htmlspecialchars($v['klaster']); ?></span>
                            
                            <?php if ($is_tersedia): ?>
                                <span class="badge badge-success" style="font-size: 11px; padding: 4px 10px;">🟢 Tersedia</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 11px; padding: 4px 10px; background: #FCE8E6; color: #A6261D;">🔴 <?= htmlspecialchars($v['status']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="villa-booking-title" style="margin-top: 15px;"><?= htmlspecialchars($v['nama_vila']); ?></h3>
                        <p class="villa-booking-address"><?= htmlspecialchars($v['alamat']); ?></p>
                    </div>

                    <div class="villa-booking-price-zone">
                        <span class="villa-price-amount">Rp <?= number_format($v['harga_per_malam'], 0, ',', '.'); ?></span>
                        <span class="villa-price-label">/ malam</span>
                    </div>
                    
                    <form action="" method="POST" class="villa-booking-form">
                        <input type="hidden" name="id_vila" value="<?= $v['id_vila']; ?>">
                        
                        <div class="form-grid-inputs">
                            <div class="form-group-inline">
                                <label>Tanggal Check-In</label>
                                <input type="date" name="tgl_checkin" min="<?= date('Y-m-d'); ?>" class="form-control-luxury" <?= $is_tersedia ? '' : 'disabled' ?> required>
                            </div>
                            
                            <div class="form-group-inline">
                                <label>Durasi (Malam)</label>
                                <div class="input-duration-wrapper">
                                    <input type="number" name="durasi_malam" min="1" max="30" class="form-control-luxury input-number-luxury" value="1" <?= $is_tersedia ? '' : 'disabled' ?> required>
                                    <span class="input-suffix">Malam</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($is_tersedia): ?>
                            <button type="submit" name="btn_booking" class="btn-booking-submit">Cek &amp; Booking Unit</button>
                        <?php else: ?>
                            <button type="button" class="btn-booking-submit" style="background: #D5C2AF; color: #806E60; cursor: not-allowed;" disabled>Unit Penuh / Tidak Tersedia</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>