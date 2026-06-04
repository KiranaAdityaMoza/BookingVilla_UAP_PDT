<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$status_type = '';

if (isset($_GET['action_status']) && isset($_GET['id_b'])) {
    $act_status = $_GET['action_status'];
    $id_b       = intval($_GET['id_b']);

    try {
        if ($act_status === 'Paid') {
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Validasi Reservasi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .sidebar a.active { background-color: #0284c7 !important; color: white !important; font-weight: bold; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #475569; border-radius: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status_type; ?>"><?= $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>📅 Validasi Transaksi Keuangan & Riwayat Reservasi</h2>
            <p style="color:#64748b; margin-bottom: 15px;">Menerapkan <b>Materi 2 (SQL JOIN Multi Tabel)</b> dan <b>Materi 5 (Built-In Function String UPPER)</b> melalui objek view <code>view_riwayat_reservasi</code>.</p>
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
                                        echo "<a href='?action_status=Paid&id_b={$r['id_booking']}' class='btn btn-success' style='padding:3px 8px; font-size:12px; text-decoration:none;'>Sahkan Pembayaran</a>";
                                    } elseif ($r['status_booking'] == 'Paid') {
                                        echo "<a href='?action_status=Cancelled&id_b={$r['id_booking']}' class='btn btn-danger' style='padding:3px 8px; font-size:12px; text-decoration:none;' onclick='return confirm(\"Batalkan paksa sewa lunas ini? Tindakan ini akan memicu log audit trigger!\")'>Batalkan Sewa</a>";
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
    </div>
</body>
</html>