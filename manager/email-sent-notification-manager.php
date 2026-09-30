<?php
require_once ("../config.php");
session_start();
if (!isset($_SESSION['email_sent'])) {
    header("Location: forgot-password-manager.php");
    exit();
}
$status = $_SESSION['email_sent'] ?? 'error';
$message = $_SESSION['email_error_message'] ?? '';
$email = $_SESSION['email'] ?? '';
unset($_SESSION['email_sent']);
unset($_SESSION['email_error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sikatugas | Email Sent</title>
  <link rel="stylesheet" href="email-sent-notification-manager.css">
</head>
<body>
<div class="container">
    <div class="left">
      <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
      <img src="../images/admin_login_illustration.png" alt="Gambar Login" class="img-center">
    </div>
    <div class="right">
    <h2>
        <?= $status === "success" ? "Berhasil Mengirim Email" : "Gagal Mengirim Email" ?>
    </h2>
    <?php if ($status === "success"): ?>
        <p class="success-message">
            Pesan berisi link untuk mengatur ulang password berhasil dikirimkan ke email 
            <b><?= htmlspecialchars($email) ?></b>. Silakan cek email Anda.<br>
            Jika pesan email tidak ada, silakan periksa folder spam atau 
            <a href="forgot-password-manager.php" class="try-again">Coba Lagi</a>.
        </p>
    <?php else: ?>
        <p class="error-message">
            Gagal mengirim<br>
            <a href="forgot-password-manager.php" class="try-again">Coba Lagi</a>. 
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <p class="back-to-login">
        <a href="login-manager.php">Kembali ke halaman Login</a>
    </p>
    </div>
  </div>
</body>
</html>
