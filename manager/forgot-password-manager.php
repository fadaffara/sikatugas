<?php
require_once("../config.php");
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "SELECT email FROM manager LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); 
        $update_sql = "UPDATE manager SET reset_token = ?, token_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sss", $token_hash, $expiry, $email);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            try {
                require_once __DIR__ . "/send-email-manager.php";
                $_SESSION['email_sent'] = "success";
                $_SESSION['email'] = $email; 
                $_SESSION['token'] = $token_hash; 
            } catch (Exception $e) {
                $_SESSION['email_sent'] = "error";
                $_SESSION['email_error_message'] = $e->getMessage();
            }
            header("Location: email-sent-notification-manager.php");
            exit();
        } else {
            $error = "Gagal memperbarui token reset password.";
        }
    } else {
        $error = "Email manager tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password Manager</title>
  <link rel="stylesheet" href="forgot-password-manager.css">
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
      <img src="../images/admin_login_illustration.png" alt="Gambar Login" class="img-center">
    </div>
    <div class="right">
      <h2>Lupa Password?</h2>
      <p>Instruksi untuk mengatur ulang password akan dikirimkan ke email manager.</p>
      <p>Silakan click tombol di bawah ini untuk mengirim pesan ke email manager</p>
      <?php if (isset($error)): ?>
        <div class="error-message">
          <p><?= htmlspecialchars($error) ?></p>
        </div>
      <?php endif; ?>
      <form method="post" action="forgot-password-manager.php"> 
        <button type="submit" class="btn">Kirim Link Reset Password</button>
      </form>
      <p class="back-to-login">
        Ingat Password? <a href="login-manager.php">Kembali ke halaman Login</a>
      </p>
    </div>
  </div>
</body>
</html>

