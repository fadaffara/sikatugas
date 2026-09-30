<?php
require_once("../config.php");
session_start();
$error = "";
if (isset($_POST['login'])) {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    if (empty($email) || empty($password)) {
        $error = "Email atau password tidak boleh kosong.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $sql = "SELECT * FROM manager WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                $_SESSION['email_admin'] = $email;
                $_SESSION['login_admin'] = true;
                $_SESSION['login_user'] = false;
                $_SESSION['name_manager'] = $row['name'];
                

                
                header("Location: manager-dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Akun tidak ditemukan.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager | Login</title>
    <link rel="stylesheet" href="login-manager.css">
  </head>
<body>
  <div class="container">
    <div class="left">
      <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
      <img src="../images/GDPR-cuate.png" alt="Ilustrasi Login" class="img-center">
    </div>
    <div class="right">
      <h2>Manager Account Login</h2>
      <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
      <form method="post" action="login-manager.php"> 
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" placeholder="Masukkan email" name="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" placeholder="Masukkan password" name="password" required>
        </div>
        <div class="forgot-password">
        <a href="forgot-password-manager.php">Lupa Password?</a>
       </div>
        <button type="submit" class="btn" name="login">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
