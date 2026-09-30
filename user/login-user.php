<?php
require("../config.php");
session_start();

if (isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        header("Location: login-user.php?error=invalid_email_format");
        exit;
    }

    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (!is_null($row['token_aktivasi'])) {
            header("Location: login-user.php?error=account_not_activated");
            exit;
        }

        if (password_verify($password, $row["password"])) {
            $photo_user = !empty($row['foto']) ? "../images/" . $row['foto'] : '../images/photo-of-user.png';

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username_user'] = $row['username'];
            $_SESSION['email_user'] = $email;
            $_SESSION['name_user'] = $row['name'];
            
            $_SESSION['photo_user'] = $photo_user;
            $_SESSION['login_user'] = true;
            $_SESSION['login_admin'] = false;

            header("Location: user-dashboard.php");
            exit;
        } else {
            header("Location: login-user.php?error=password_invalid");
            exit;
        }
    } else {
        header("Location: login-user.php?error=email_not_registered");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User | Login</title>
    <link rel="stylesheet" href="login-user.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
            <img src="../images/user_login_illustration.png" alt="Ilustrasi Login" class="img-center">
        </div>
        <div class="right">
            <h2>User Account Login</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    if ($_GET['error'] === 'password_invalid') {
                        echo "Password salah!";
                    } elseif ($_GET['error'] === 'email_not_registered') {
                        echo "Akun belum terdaftar!";
                    } elseif ($_GET['error'] === 'account_not_activated') {
                        echo "Akun Anda belum diaktivasi. Silakan cek email untuk aktivasi akun.";
                    }
                    ?>
                </div>
            <?php endif; ?>
            <form action="login-user.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password-user.php">Lupa Password?</a>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <p class="register">Belum memiliki akun? 
                <a href="register-user.php" class="register-link">Buat akun di sini</a>
            </p>
        </div>
    </div>
</body>
</html>


