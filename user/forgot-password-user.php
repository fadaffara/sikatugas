<?php
require_once("../config.php");
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $sql = "SELECT email FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $token = bin2hex(random_bytes(16));
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 60 * 30); 
            $update_sql = "UPDATE users SET token_reset = ?, token_kedaluwarsa = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $token_hash, $expiry, $email);
            $update_stmt->execute();
            if ($update_stmt->affected_rows > 0) {
                try {
                    require_once __DIR__ . "/send-reset-email-user.php";
                    $_SESSION['email_sent'] = "success";
                    $_SESSION['email'] = $email; 
                    $_SESSION['token'] = $token; 
                } catch (Exception $e) {
                    $_SESSION['email_sent'] = "error";
                    $_SESSION['email_error_message'] = $e->getMessage();
                }
                header("Location: email-sent-notification-user.php");
                exit();
            } else {
                $error = "Gagal memperbarui token reset password.";
            }
        } else {
            $error = "Email pengguna tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot-password-user.css">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
        <img src="../images/user_login_illustration.png" alt="Gambar Login" class="img-center">
    </div>
    <div class="right">
        <h2>Lupa Password?</h2>
        <p>Silakan masukkan email untuk mengatur ulang password Anda.</p>
        <p>Instruksi untuk mengatur ulang password akan dikirimkan ke email Anda.</p>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="forgot-password-user.php">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="Masukkan email" required>
            <button type="submit" class="btn">Kirim Link Reset Password</button>
        </form>
        <p class="back-to-login">
            Ingat Password? <a href="login-user.php">Kembali ke halaman Login</a>
        </p>
    </div>
</div>
</body>
</html>
