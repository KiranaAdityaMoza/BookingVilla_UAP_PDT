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
    <title>Login - Jaringan Booking Vila</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-container">

    <div class="login-box" style="max-width: 450px;">
        <h2 style="text-align: center; color: #0f172a; margin-bottom: 10px;">🏨 Jaringan Vila</h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 25px; font-size: 14px;">Pantai & Puncak Ruang Reservasi</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="padding: 10px; font-size: 14px;">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username..." required>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password..." required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Masuk ke Sistem</button>
        </form>
        
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #cbd5e1; font-size: 12px; color: #64748b; line-height: 1.6;">
            <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">🔑 Pilihan Akun Demo Sistem:</p>
            <table style="width: 100%; margin-top: 5px; font-size: 11px;">
                <tr style="background: #f8fafc;">
                    <td style="padding: 4px;"><b>Role</b></td>
                    <td style="padding: 4px;"><b>Username</b></td>
                    <td style="padding: 4px;"><b>Password</b></td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Admin Pusat</td>
                    <td style="padding: 4px;"><code style="background:#e2e8f0; padding:2px 4px; border-radius:3px;">admin</code></td>
                    <td style="padding: 4px;">admin123</td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Cust: Kirana</td>
                    <td style="padding: 4px;"><code style="background:#e2e8f0; padding:2px 4px; border-radius:3px;">kirana</code></td>
                    <td style="padding: 4px;">kirana123</td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Cust: Khaila</td>
                    <td style="padding: 4px;"><code style="background:#e2e8f0; padding:2px 4px; border-radius:3px;">khaila</code></td>
                    <td style="padding: 4px;">khaila123</td>
                </tr>
                <tr>
                    <td style="padding: 4px;">Cust: Naura</td>
                    <td style="padding: 4px;"><code style="background:#e2e8f0; padding:2px 4px; border-radius:3px;">naura</code></td>
                    <td style="padding: 4px;">naura123</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>