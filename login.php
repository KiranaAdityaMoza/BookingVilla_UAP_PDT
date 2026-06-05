<?php
require_once 'config.php';
session_start();

// Jika sudah login, langsung alihkan ke halaman masing-masing
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_villa.php');
    } else {
        header('Location: customer_dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // [MATERI 2: SQL JOIN] 
            // Menggabungkan tabel users dan customer menggunakan LEFT JOIN
            // untuk mendeteksi profil customer jika role-nya adalah customer.
            $query = "SELECT u.*, c.nama 
                      FROM users u 
                      LEFT JOIN customer c ON u.id_customer_fk = c.id_customer 
                      WHERE u.username = :username AND u.password = :password";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'username' => $username,
                'password' => $password // Plain text sesuai spek database kita
            ]);
            
            $user = $stmt->fetch();

            if ($user) {
                // Set Session data login
                $_SESSION['id_user']     = $user['id_user'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['id_customer'] = $user['id_customer_fk'];
                $_SESSION['nama_user']   = ($user['role'] === 'admin') ? 'Administrator' : $user['nama'];

                // Alihkan halaman berdasarkan role
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: customer_dashboard.php');
                }
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    } else {
        $error = 'Semua field wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jaringan Booking Vila</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <div class="login-box">
        <h2 style="text-align: center; color: #0f172a; margin-bottom: 6px; font-weight: 700; font-size: 24px;">Jaringan Vila</h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 28px; font-size: 14px;">Pantai & Puncak Ruang Reservasi</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username..." required autocomplete="off">
            </div>
            
            <div class="form-group" style="margin-bottom: 28px;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password..." required>
            </div>
            
            <button type="submit" class="btn-primary">Masuk ke Sistem</button>
        </form>
    </div>

</body>
</html>