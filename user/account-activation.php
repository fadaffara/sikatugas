<?php 
require_once("../config.php");
$verification_token_hash = $_GET["token"];
$sql = "SELECT * FROM users WHERE token_aktivasi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $verification_token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user === null) {
  die("Token tidak ada");
}
$verified = "UPDATE users SET token_aktivasi = NULL WHERE id = ?";
$stmt = $conn->prepare($verified);
$stmt->bind_param("s", $user["id"]);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun</title>
</head>
<body>
        <h1>Verifikasi Akun Berhasil !</h1>
        <p>Akun Anda berhasil diverifikasi. Anda bisa login sekarang</p>
        <p>Login<a href="login-user.php">di sini</a></p>
</body>
</html>
