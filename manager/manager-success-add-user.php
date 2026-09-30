<?php
session_start();

// Cek apakah user datang dari halaman yang valid (sesudah submit form berhasil)
if (!isset($_SESSION['token'])) {
    header("Location: manager-dashboard.php");
    exit;
}

// Hapus token sesudah sukses
unset($_SESSION['token']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Sukses</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Selamat, user baru berhasil ditambahkan!</h1>
        <p>Pengguna baru telah berhasil didaftarkan. Email verifikasi telah dikirimkan ke alamat email pengguna. Pastikan mereka memverifikasi email mereka agar bisa login.</p>
        <a href="manager-dashboard.php" class="btn">Kembali ke Dashboard Admin</a>
    </div>
</body>
</html>
