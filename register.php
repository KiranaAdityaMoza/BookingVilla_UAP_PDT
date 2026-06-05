<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'customer_dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmtCheck->execute(['username' => $username]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                $error = 'Username sudah terdaftar! Silakan gunakan nama lain.';
            } else {
                $queryInsert = "INSERT INTO users (username, password, role, id_customer_fk) 
                                VALUES (:username, :password, 'customer', NULL)";
                $stmtInsert = $pdo->prepare($queryInsert);
                $stmtInsert->execute([
                    'username' => $username,
                    'password' => $password
                ]);

                $_SESSION['reg_success'] = 'Pendaftaran berhasil! Silakan login menggunakan akun baru Anda.';
                header('Location: login.php');
                exit;
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
    <title>Register - Jaringan Booking Vila</title>
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
                <h2>Daftar Akun Baru</h2>
                <p>Silakan isi username dan password Anda</p>
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
                           placeholder="Buat username" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Buat password" required>
                </div>

                <button type="submit" class="btn-login">
                    Daftar Akun
                </button>

                <p style="margin-top: 15px; text-align: center; font-size: 14px;">
                    Sudah punya akun? <a href="login.php" style="color: #0f172a; font-weight: 600; text-decoration: none;">Login di sini</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>