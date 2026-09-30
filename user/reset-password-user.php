<?php
require_once("../config.php");
$token_hash = $_GET["token"];
$sql = "SELECT * FROM users WHERE token_reset = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user === null) {
  die("Token tidak ada");
}
if (strtotime($user["token_kedaluwarsa"]) <=time()) {
  die("Token sudah kedaluwarsa");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikatugas | Reset Password</title>
    <link rel="stylesheet" href="reset-password-user.css">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
        <img src="../images/user_login_illustration.png" alt="Gambar Login" class="img-center">
    </div>
    <div class="right">
        <h1>Reset Password User</h1>
            <form method="POST" action="process-reset-password-user.php">
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