<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: register-user.php");
    exit;
}
unset($_SESSION['token']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Berhasil</title>
    <link rel="stylesheet" href="signup-success-user.css">
</head>
<body>
    <div class="container">
        <h1>Registrasi Berhasil!</h1>
        <p>Akun Anda telah berhasil dibuat. Silakan cek email Anda untuk memverifikasi akun.</p>
        <a href="login-user.php">Login</a>
       
    </div>
</body>
</html>
