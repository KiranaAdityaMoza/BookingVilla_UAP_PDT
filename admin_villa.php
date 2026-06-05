<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_dashboard.php');
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
            $message = "Berhasil Menambah Vila Baru via Stored Procedure!";
            $status_type = "success";
        } elseif ($action === 'UPDATE') {
            $stmt = $pdo->prepare("CALL sp_manage_vila('UPDATE', :id, '', '', '', :harga, :status)");
            $stmt->execute(['id'=>$id, 'harga'=>$harga, 'status'=>$status]);
            $message = "Berhasil Memperbarui Properti via Stored Procedure!";
            $status_type = "success";
        } elseif ($action === 'DELETE') {
            $stmt = $pdo->prepare("CALL sp_manage_vila('DELETE', :id, '', '', '', 0, '')");
            $stmt->execute(['id'=>$id]);
            $message = "Berhasil Menghapus Vila via Stored Procedure!";
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

    <div class="page-header">
        <h1>🏡 Booking Villa Admin</h1>
        <p>Kelola Villa Puncak & Pantai</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $status_type; ?>">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <h2>Kelola Data Vila</h2>

        <p class="subtitle">
            Implementasi Stored Procedure
            <code>CALL sp_manage_vila()</code>
        </p>

        <form action="" method="POST" class="villa-form">

            <input type="hidden" name="action_crud" value="INSERT">

            <h3>➕ Tambah Vila Baru</h3>

            <div class="form-grid-3">

                <input type="text"
                       name="id_vila"
                       placeholder="ID Vila (V11)"
                       class="form-control"
                       required>

                <input type="text"
                       name="nama_vila"
                       placeholder="Nama Vila"
                       class="form-control"
                       required>

                <select name="klaster" class="form-control">
                    <option value="Puncak">🏔️ Puncak</option>
                    <option value="Pantai">🌊 Pantai</option>
                </select>

            </div>

            <div class="form-grid-2">

                <input type="text"
                       name="alamat"
                       placeholder="Alamat Lengkap"
                       class="form-control"
                       required>

                <input type="number"
                       name="harga_per_malam"
                       placeholder="Harga per Malam"
                       class="form-control"
                       required>

            </div>

            <button type="submit" class="btn-save">
                Simpan Vila
            </button>

        </form>

        <table class="villa-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Vila</th>
                    <th>Klaster</th>
                    <th>Alamat</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $stmtVila = $pdo->query("SELECT * FROM vila ORDER BY id_vila ASC");

                while ($v = $stmtVila->fetch()) :
                ?>

                <tr>

                    <td><strong><?= $v['id_vila']; ?></strong></td>

                    <td><?= $v['nama_vila']; ?></td>

                    <td>
                        <span class="badge">
                            <?= $v['klaster']; ?>
                        </span>
                    </td>

                    <td><?= $v['alamat']; ?></td>

                    <td>
                        Rp <?= number_format($v['harga_per_malam'],0,',','.'); ?>
                    </td>

                    <td>
                        <?php if($v['status']=='Tersedia'): ?>
                            <span class="badge badge-success">Tersedia</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Disewa</span>
                        <?php endif; ?>
                    </td>

                    <td>

                        <form action="" method="POST" class="inline-form">

                            <input type="hidden"
                                   name="action_crud"
                                   value="UPDATE">

                            <input type="hidden"
                                   name="id_vila"
                                   value="<?= $v['id_vila']; ?>">

                            <input type="number"
                                   name="harga_per_malam"
                                   value="<?= $v['harga_per_malam']; ?>">

                            <select name="status">

                                <option value="Tersedia"
                                <?= ($v['status']=='Tersedia')?'selected':'' ?>>
                                    Tersedia
                                </option>

                                <option value="Disewa"
                                <?= ($v['status']=='Disewa')?'selected':'' ?>>
                                    Disewa
                                </option>

                            </select>

                            <button type="submit" class="btn-update">
                                Ubah
                            </button>

                        </form>

                        <form action=""
                              method="POST"
                              class="inline-form"
                              onsubmit="return confirm('Hapus vila ini?')">

                            <input type="hidden"
                                   name="action_crud"
                                   value="DELETE">

                            <input type="hidden"
                                   name="id_vila"
                                   value="<?= $v['id_vila']; ?>">

                            <button type="submit" class="btn-delete">
                                Hapus
                            </button>

                        </form>

                    </td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>