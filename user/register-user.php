<?php 
require_once ("../config.php");
session_start();

$username_error = '';
$email_error = '';
$password_error = '';
$photo_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        die("Format email harus valid (contoh: emailmu@gmail.com)");
    }

    // Validasi password
    if (strlen($_POST['password']) < 8) {
        die("Password harus berisi setidaknya 8 karakter");
    }
    if (!preg_match("/[a-z]/i", $_POST['password'])) {
        die("Password harus berisi setidaknya 1 huruf");
    }
    if (!preg_match("/[0-9]/", $_POST['password'])) {
        die("Password harus berisi setidaknya 1 angka");
    }
    if ($_POST["password"] !== $_POST["confirm_password"]) {
        $password_error = "Password dan konfirmasi password tidak cocok";
    }

    // Validasi username dan email
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];

    $checkUsernameQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkUsernameQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $username_error = "Username sudah terdaftar!";
    }

    $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $email_error = "Email sudah terdaftar!";
    }

  
    $photo_name = 'photo-of-user.png'; 
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo']['type'];
        $file_size = $_FILES['photo']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $photo_error = "Format file harus JPEG, PNG, atau GIF";
        } elseif ($file_size > 4 * 1024 * 1024) { 
            $photo_error = "Ukuran file tidak boleh lebih dari 2MB";
        } else {
            $photo_name = uniqid() . '-' . basename($_FILES['photo']['name']);
            $photo_path = '../images/' . $photo_name;

            if (!is_dir('../images')) {
                mkdir('../images', 0777, true);
            }

            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                $photo_error = "Gagal mengupload foto";
                $photo_name = 'photo-of-user.png'; 
            }
        }
    }

   
    if (empty($username_error) && empty($email_error) && empty($password_error) && empty($photo_error)) {
        $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(16));
        $verification_token_hash = hash("sha256", $verification_token);

        $sql = "INSERT INTO users (name, username, email, password, foto, token_aktivasi) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $username, $email, $password_hash, $photo_name, $verification_token_hash);

        if ($stmt->execute()) {
            try {
                require_once __DIR__ . "/send-email-user.php"; 
                $_SESSION['token'] = $verification_token_hash; 
                header("Location: success-register-user.php");
                exit;
            } catch (Exception $e) {
                echo "Pesan gagal dikirim. {$mail->ErrorInfo}";
                exit;
            }
        } else {
            die("Terjadi kesalahan saat registrasi: " . $conn->error);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register User</title>
  <link rel="stylesheet" href="register-user.css">
  <script src="register-user.js"></script>
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="../images/sikatugas-logo.png" alt="Logo" class="logo">
      <img src="../images/user_register_illustration.png" alt="Gambar Register" class="img-center">
    </div>
    <div class="right">
      <h2>User Register Account</h2>
        <?php if (!empty($username_error) || !empty($email_error) || !empty($password_error) || !empty($photo_error)): ?>
            <div class="error-message">
                <?php
                if (!empty($username_error)) {
                    echo "<p>{$username_error}</p>";
                }
                if (!empty($email_error)) {
                    echo "<p>{$email_error}</p>";
                }
                if (!empty($password_error)) {
                    echo "<p>{$password_error}</p>";
                }
                if (!empty($photo_error)) {
                    echo "<p>{$photo_error}</p>";
                }
                ?>
            </div>
        <?php endif; ?>
      <form id="registerForm" action="register-user.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" placeholder="Masukkan nama" required>
        </div>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Masukkan email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password</label>
          <input type="password" id="confirm-password" name="confirm_password" placeholder="Konfirmasi password" required>
        </div>
        <div class="file-upload-container">
          <div class="file-label-container">
            <label for="photo" class="file-label">Pilih <br> Foto Profil</label>
          </div>
          <div class="file-name-container">
            <span id="file-name" class="file-name">Belum dipilih</span>
          </div>
          <input type="file" id="photo" name="photo" />
        </div>

        <button type="submit" class="btn">Register Account</button>
      </form>
      <p class="login">Sudah memiliki akun? <a href="login-user.php" class="login-link">Login di sini</a></p>
    </div>
  </div>
</body>
</html>
