<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

/**
 * Fungsi untuk mengambil dan menampilkan data tabel berdasarkan view database
 */
function renderVilaTable($pdo, $viewName, $title) {
    echo "<div class='card-header-academic' style='margin-top: 35px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px; margin-bottom: 15px;'>";
    echo "  <h3 style='margin: 0; color: #0f172a; font-size: 18px; display: flex; align-items: center; gap: 8px;'>🏡 $title</h3>";
    echo "</div>";
    
    // Menggunakan class .table-custom dari style.css global yang baru
    echo "<table class='table-custom'>
            <thead>
                <tr>
                    <th style='width: 12%;'>ID PROPERTI</th>
                    <th style='width: 25%;'>NAMA VILA</th>
                    <th style='width: 40%;'>ALAMAT LENGKAP</th>
                    <th style='width: 13%;'>HARGA / MALAM</th>
                    <th style='width: 10%;'>STATUS TERKINI</th>
                </tr>
            </thead>
            <tbody>";
    
    try {
        $stmt = $pdo->query("SELECT * FROM $viewName");
        $rows = $stmt->fetchAll();
        
        if (count($rows) == 0) {
            echo "<tr><td colspan='5' style='text-align:center; color: #94a3b8; padding: 24px;'>Tidak ada vila aktif yang tersedia di wilayah ini.</td></tr>";
        } else {
            foreach ($rows as $row) {
                // Mapping column name untuk mengantisipasi perbedaan snake_case di database
                $id_vila = $row['id_vila'] ?? $row['id_properti'] ?? '-';
                $nama_vila = $row['nama_vila'] ?? '-';
                $alamat = $row['alamat'] ?? $row['alamat_lokasi'] ?? '-';
                $harga = $row['harga_per_malam'] ?? $row['harga_malam'] ?? 0;
                $status = $row['status'] ?? $row['status_kamar'] ?? 'Tersedia';

                echo "<tr>
                        <td><b>{$id_vila}</b></td>
                        <td class='td-longtext'><b>{$nama_vila}</b></td>
                        <td>{$alamat}</td>
                        <td class='txt-price'>Rp " . number_format($harga, 0, ',', '.') . "</td>
                        <td><span class='badge-status badge-success'>{$status}</span></td>
                      </tr>";
            }
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='5' style='text-align:center; color:#dc2626; padding: 24px;'>Gagal memuat data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
    
    echo "</tbody></table>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Wilayah - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <h2 style="margin: 0 0 6px 0; font-size: 24px; color: #0f172a;">🌅 Katalog Wilayah Distribusi Vila</h2>
            <p style="color: #64748b; margin: 0 0 10px 0; font-size: 14px;">
                Menerapkan <b>Materi 1: Database Views</b>. Menampilkan data real-time langsung dari filter query view spesifik.
            </p>
            
            <?php 
                // 1. Render Wilayah Pantai
                renderVilaTable($pdo, 'view_vila_pantai_aktif', 'Katalog Khusus Wilayah Vila Pantai (Aktif)'); 
                
                // 2. Render Wilayah Puncak
                renderVilaTable($pdo, 'view_vila_puncak_aktif', 'Katalog Khusus Wilayah Vila Puncak (Aktif)'); 
            ?>
        </div>
    </div>

</body>
</html>