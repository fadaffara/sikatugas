<?php
require_once("../config.php");
$token_hash = $_POST["token"];
$sql = "SELECT * FROM manager WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
if ($admin === null) {
  die("Token tidak ada");
}
if (strtotime($admin["token_expiry"]) <=time()) {
  die("Token sudah kedaluwarsa");
}
if (strlen($_POST["new_password"]) < 8) {
  die("Password harus berisi setidaknya 8 karakter");
}
if ( ! preg_match ("/[a-z]/i", $_POST["new_password"])) {
  die("Password harus berisi setidaknya 1 huruf");
}
if ( ! preg_match ("/[0-9]/", $_POST["new_password"])) {
  die("Password harus berisi setidaknya 1 angka");
}
if ($_POST["new_password"] !== $_POST["confirm_password"]) {
  die("Password dan konfirmasi password tidak cocok");
}
$password_hash = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
$resetPW = "UPDATE manager SET password = ?, reset_token = null, token_expiry = null WHERE id = ?";
$stmt = $conn->prepare($resetPW);
$stmt->bind_param("si", $password_hash, $admin["id"]);
$stmt->execute();
echo "Password berhasil diupdate. Anda bisa login sekarang". "<br>";
echo "Login <a href='login-manager.php'> di sini </a>";
?>
