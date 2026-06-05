<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
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
            $query = "SELECT u.*, c.nama 
                      FROM users u 
                      LEFT JOIN customer c ON u.id_customer_fk = c.id_customer 
                      WHERE u.username = :username AND u.password = :password";

            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'username' => $username,
                'password' => $password
            ]);

            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['id_user']     = $user['id_user'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['id_customer'] = $user['id_customer_fk'];
                $_SESSION['nama_user']   = ($user['role'] === 'admin') ? 'Administrator' : $user['nama'];

                header('Location: ' . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'customer_dashboard.php'));
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

<div class="login-container animate-fade-up">

    <div class="login-left">
        <div class="login-left-content">
            <h1 class="brand-title">🏡 Villaku</h1>
            <p class="brand-tagline">
                Temukan pengalaman menginap terbaik di Villa Puncak dan Villa Pantai.
                Nikmati reservasi yang mudah, cepat dan nyaman.
            </p>
        </div>
    </div>

    <div class="login-right">
        <div class="login-content">

            <div class="login-header">
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk melanjutkan ke sistem</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                           placeholder="Masukkan username" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-login">
                    Masuk ke Sistem
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>