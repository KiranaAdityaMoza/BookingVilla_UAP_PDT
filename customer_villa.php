<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$id_customer = $_SESSION['id_customer'];
$message     = '';
$status_type = '';

// Proses Simpan Transaksi Booking dengan Proteksi Jadwal Bentrok Akurat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_booking'])) {
    $id_vila     = $_POST['id_vila'];
    $tgl_checkin = $_POST['tgl_checkin']; // Format otomatis dari HTML: YYYY-MM-DD
    $durasi      = intval($_POST['durasi_malam']);

    if (!empty($id_vila) && !empty($tgl_checkin) && $durasi > 0) {
        try {
            // 1. Amankan & Hitung Tanggal Check-In dan Check-Out dalam format standar SQL (YYYY-MM-DD)
            $checkin_baru  = date('Y-m-d', strtotime($tgl_checkin));
            $checkout_baru = date('Y-m-d', strtotime($tgl_checkin . " + " . $durasi . " days")); 
            // Contoh: Masuk 03 Juni + 5 malam = Murni Check-out tanggal 08 Juni 2026

            // 2. ALGORITMA OVERLAP CHECKING INTERNASIONAL
            // Rumus: CheckIn_Baru < CheckOut_Lama DAN Checkout_Baru > CheckIn_Lama
            // DATE_ADD(tgl_checkin, INTERVAL durasi_malam DAY) digunakan untuk menghitung tanggal checkout riwayat lama
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
                // JIKA BENTROK: Tembak Peringatan Gagal
                $message = "❌ Maaf, Vila ini sudah dipesan pada rentang tanggal tersebut. Silakan tentukan tanggal berkunjung yang lain!";
                $status_type = "danger";
            } else {
                // JIKA AMAN: Jalankan Query Pengisian Data
                $stmtVila = $pdo->prepare("SELECT harga_per_malam FROM vila WHERE id_vila = :id");
                $stmtVila->execute(['id' => $id_vila]);
                $vila = $stmtVila->fetch();

                if ($vila) {
                    $harga_per_malam = $vila['harga_per_malam'];
                    $total_harga_asli = $harga_per_malam * $durasi;

                    // [MATERI 5: CUSTOM FUNCTION]
                    $stmtDiskon = $pdo->prepare("SELECT GetDiskonVila(:total) AS nominal_potongan");
                    $stmtDiskon->execute(['total' => $total_harga_asli]);
                    $diskonData = $stmtDiskon->fetch();
                    
                    $potongan_diskon   = $diskonData['nominal_potongan'];
                    $total_bayar_bersih = $total_harga_asli - $potongan_diskon;

                    // Simpan data transaksi ke tabel booking (status awal selalu 'Pending')
                    $queryInsert = "INSERT INTO booking (id_customer, id_vila, tgl_checkin, durasi_malam, total_harga_asli, potongan_diskon, total_bayar_bersih, status_booking) 
                                    VALUES (:id_cust, :id_v, :tgl, :durasi, :asli, :potongan, :bersih, 'Pending')";
                    
                    $stmtInsert = $pdo->prepare($queryInsert);
                    $stmtInsert->execute([
                        'id_cust'   => $id_customer,
                        'id_v'      => $id_vila,
                        'tgl'       => $checkin_baru, // Masuk format YYYY-MM-DD ke database
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

// Mengambil semua daftar master vila untuk ditampilkan di katalog
$villas = $pdo->query("SELECT * FROM vila ORDER BY klaster ASC, id_vila ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Vila - Jaringan Vila</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grid-katalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .villa-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 20px; border: 1px solid #e2e8f0; }
        .villa-klaster { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-bottom: 10px; }
        .puncak { background-color: #dbeafe; color: #1e40af; }
        .pantai { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

    <?php include 'customer_navbar.php'; ?>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <h2 style="color: #1e293b; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px; margin-bottom: 5px;">🌟 Pilihan Properti Vila</h2>
        <p style="color: #64748b; margin-bottom: 25px;">Sistem pintar otomatis mendeteksi ketersediaan jadwal secara <i>real-time</i> berdasarkan kalender pesanan lunas.</p>
        
        <div class="grid-katalog">
            <?php foreach ($villas as $v): ?>
                <div class="villa-card">
                    <span class="villa-klaster <?= strtolower($v['klaster']); ?>">Klaster <?= $v['klaster']; ?></span>
                    <h3 style="margin-bottom: 5px; color: #0f172a;"><?= htmlspecialchars($v['nama_vila']); ?></h3>
                    <p style="font-size: 13px; color: #64748b; min-height: 40px; margin-bottom: 10px;"><?= htmlspecialchars($v['alamat']); ?></p>
                    <p style="font-size: 16px; font-weight: bold; color: #0284c7; margin-bottom: 15px;">
                        Rp <?= number_format($v['harga_per_malam'], 0, ',', '.'); ?> <span style="font-size: 12px; font-weight: normal; color: #64748b;">/ malam</span>
                    </p>
                    
                    <form action="" method="POST" style="border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                        <input type="hidden" name="id_vila" value="<?= $v['id_vila']; ?>">
                        
                        <div class="form-group">
                            <label style="font-size: 12px; color: #475569;">Tanggal Check-In</label>
                            <input type="date" name="tgl_checkin" min="<?= date('Y-m-d'); ?>" class="form-control" style="padding: 6px;" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 12px; color: #475569;">Durasi Menginap (Malam)</label>
                            <input type="number" name="durasi_malam" min="1" max="30" class="form-control" style="padding: 6px;" value="1" required>
                        </div>

                        <button type="submit" name="btn_booking" class="btn btn-success" style="width: 100%; padding: 8px;">Cek & Booking Unit</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>