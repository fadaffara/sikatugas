<?php

$error_type = isset($_GET['error']) ? $_GET['error'] : null;
$error_message = '';

if ($error_type === 'invalid_token') {
    $error_message = 'Token tidak valid. Silakan cek kembali link yang Anda gunakan.';
} elseif ($error_type === 'expired_token') {
    $error_message = 'Token telah kedaluwarsa. Silakan request reset password baru.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikatugas | Reset Error</title>
    <link rel="stylesheet" href="reset-error-admin.css">
</head>
<body>
    <div class="container">
        <div class="left">
      <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">

      <img src="../images/admin_login_illustration.png" alt="Gambar Login" class="img-center">
       </div>

       <div class="right">
            <h2>Error: Reset Password</h2>
            <div class="error-message">
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
            <a href="reset-password-admin.php" class="link">Kembali ke Halaman Reset Password</a>
        </div>
  </div>
</body>
</html>
