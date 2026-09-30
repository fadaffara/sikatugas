<?php
if (!isset($_GET['success']) || $_GET['success'] !== 'true') {
    header("Location: reset-password-admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikatugas | Reset Password Admin Berhasil</title>
    <link rel="stylesheet" href="reset-success-admin.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
            <img src="../images/admin_login_illustration.png" alt="Gambar Login" class="img-center">
        </div>
        <div class="right">
            <?php if ($reset_success): ?>
                <h2>Password Berhasil Direset!</h2>
                <div class="success-message">
                    <p>Password Anda telah berhasil diperbarui. Silakan gunakan password baru Anda untuk login.</p>
                </div>
                <a href="login-admin.php" class="link">Kembali ke Halaman Login</a>
            <?php else: ?>
                <h2>Terjadi Kesalahan</h2>
                <div class="error-message">
                    <p>Proses reset password gagal. Silakan coba lagi nanti atau hubungi admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
