<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Wilayah Pantai</title>
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
        <div class="card">
            <h2>🏖️ Katalog Khusus Wilayah Vila Pantai (Aktif)</h2>
            <p style="color:#64748b; margin-bottom: 15px;">Menerapkan <b>Materi 1: Database Views</b>. Data di bawah ditarik langsung objek virtual view <code>view_vila_pantai_aktif</code> (Hanya menyaring klaster Pantai yang berstatus Tersedia saja).</p>
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
    </div>
</body>
</html>