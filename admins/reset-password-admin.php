<?php
require_once("../config.php");
$token_hash = $_GET["token"];
$sql = "SELECT * FROM admin WHERE reset_token = ?";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikatugas | Reset Password Admin</title>
    <link rel="stylesheet" href="reset-password-admin.css">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
        <img src="../images/admin_login_illustration.png" alt="Gambar Login" class="img-center">
    </div>
    <div class="right">
        <h1>Reset Password Admin</h1>
            <form method="POST" action="process-reset-password-admin.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token_hash) ?>">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" autocomplete="off" required>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" autocomplete="off" required>
                <button type="submit" class="btn">Reset Password</button>
            </form>
    </div>
</div>
</body>
</html>