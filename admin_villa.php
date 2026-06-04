<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$status_type = '';

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Manajemen Vila</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { height: 100vh !important; position: fixed !important; top: 0; left: 0; overflow-y: auto !important; }
        .main-content { margin-left: 260px !important; width: calc(100% - 260px) !important; }
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
            <h2>📦 CRUD Pengelolaan Kamar Vila Cabang</h2>
            <p style="color:#64748b; margin-bottom: 15px;">Menerapkan <b>Materi 6: Stored Procedure</b> menggunakan pemanggilan <code>CALL sp_manage_vila()</code> di backend PHP.</p>
            
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
    </div>
</body>
</html>