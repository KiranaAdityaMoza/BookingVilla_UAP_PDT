<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Reservasi - Admin Kendali</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header-academic" style="border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px; margin-bottom: 10px;">
                <h2 style="margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px; color: #0f172a;">
                    📅 Validasi Transaksi Keuangan & Riwayat Reservasi
                </h2>
                <p class="academic-note" style="margin: 0; color: #64748b; font-size: 13px;">
                    Menerapkan <b>Materi 2 (SQL JOIN Multi Tabel)</b> dan <b>Materi 5 (Built-In Function String UPPER)</b> melalui objek view <code>view_riwayat_reservasi</code>.
                </p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID BOOKING</th>
                        <th>NAMA CUSTOMER (UPPERCASE)</th>
                        <th>NAMA VILA</th>
                        <th>KLASTER</th>
                        <th>TANGGAL CHECK-IN</th>
                        <th>DURASI</th>
                        <th>TOTAL BERSIH</th>
                        <th>STATUS TRANSAKSI</th>
                        <th style="text-align: center;">AKSI ADMIN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rowsRes)): ?>
                        <?php foreach ($rowsRes as $r): ?>
                        <tr>
                            <td><b>#B0<?= htmlspecialchars($r['id_booking'] ?? '') ?></b></td>
                            
                            <td><?= htmlspecialchars($r['nama_customer_kapital'] ?? '') ?></td>
                            
                            <td><?= htmlspecialchars($r['nama_vila'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-secondary"><?= htmlspecialchars($r['klaster'] ?? '-') ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['tgl_checkin'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['durasi'] ?? '0') ?> Malam</td>
                            
                            <td style="color: #16a34a; font-weight: 600;">
                                Rp <?= number_format($r['total_bersih'] ?? 0, 0, ',', '.') ?>
                            </td>
                            
                            <td>
                                <?php if (($r['status_booking'] ?? '') === 'Pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="text-align: center;">
                                <?php if (($r['status_booking'] ?? '') === 'Pending'): ?>
                                    <a href="?action_status=Paid&id_b=<?= $r['id_booking'] ?>" class="btn btn-success" style="font-size: 12px; padding: 6px 12px; text-decoration: none; display: inline-block;">
                                        Sahkan Pembayaran
                                    </a>
                                <?php else: ?>
                                    <a href="?action_status=Pending&id_b=<?= $r['id_booking'] ?>" class="btn btn-danger" style="font-size: 12px; padding: 6px 12px; text-decoration: none; display: inline-block;" onclick="return confirm('Batalkan validasi pembayaran ini?')">
                                        Batalkan Sewa
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #94a3b8; padding: 24px;">
                                Tidak ada data reservasi yang ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>