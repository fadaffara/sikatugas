<?php 
require_once ("../config.php");

session_start(); 

if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php"); 
    exit;
}


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
            $photo_error = "Ukuran file tidak boleh lebih dari 4MB";
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
              require_once(__DIR__ . "/../user/send-email-user.php");

                $_SESSION['token'] = $verification_token_hash; 
                header("Location: manager-success-add-user.php");
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


$default_photo = "../images/photo-of-admin.jpg"; 
$sql = "SELECT photo, name, email FROM manager WHERE id = 1"; 
$result = $conn->query($sql);
      
  if ($result->num_rows > 0) {
          $admin = $result->fetch_assoc();
          $photo = $admin['photo'] ? "../images/" . $admin['photo'] : $default_photo;
    } else {
          $admin = ['name' => 'Nama tidak ditemukan', 'email' => 'Email tidak ditemukan'];
          $photo = $default_photo;
    }
 ?>
 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Tambah Data User Baru</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="manager-user-add.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="manager-show-profile.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

</head>
<body>

 
<aside class="sidebar">
    <div class="logo">
        <a href="manager-dashboard.php">
            <img src="../images/sikatugas-logo.png" alt="Logo Sikatugas" class="logo">
        </a>
    </div>

    <div class="menu">
        <div class="item"><a href="manager-dashboard.php"><i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="manager-tasks.php"><i class="fas fa-solid fa-list-check"></i>Data Tugas User</a></div>
        <div class="item"><a href="manager-insert.php"><i class="fas fa-solid fa-plus"></i>Tambah Tugas </a></div>
        <div class="item"><a href="manager-user-list.php"><i class="fa-solid fa-users"></i>Data User</a></div>
        <div class="item"><a href="manager-user-add.php" class="active"><i class="fas fa-solid fa-user-plus"></i>Tambah User</a></div>
        <div class="item"><a href="manager-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="manager-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-manager.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>

</aside>


<header> 


  <div class="notification">
    <!-- <i class="fas fa-bell"></i> -->
  </div>

  
  <div class="profile">
    <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img" onclick="toggleDetails()" 
        onerror="this.src='<?= $default_photo; ?>'">
    <div class="profile-details">
      <div class="profile-info">

      <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-sub-img" 
           onerror="this.src='<?= $default_photo; ?>'">
           
            <div class="profile-text">
                <p id="name"><?= htmlspecialchars($admin['name']);?></p>  
                <p>Admin</p>  
                <p id="email"><?= htmlspecialchars($admin['email']);?></p>  
            </div>
            
            <div class="profile-actions">
            <button class="edit-btn">
               <a href="manager-profile.php"><i class="fas fa-user-edit"></i>  &nbsp;   Edit Profil</a>
            </button>
            <button class="logout-btn" onclick="logout()">
               <a href="logout-manager.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
            </button>
            </div>
        
      </div>
    </div>
</header>
<main>
<h2>Form Tambah User Baru</h2>
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
      <form id="registerForm" action="manager-user-add.php" method="POST" enctype="multipart/form-data">
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
        <div class="form-group">
          <label for="photo" class="file-label">Pilih Foto Profil</label>
          <input type="file" id="photo" name="photo" />
        </div>

        <button type="submit" class="btn">Register Account</button>
      </form>
</main>

</body>
</html>