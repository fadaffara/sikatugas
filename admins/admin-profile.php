<?php 
require_once ("../config.php");
session_start(); 
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-admin.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Profil </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin-profile.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="admin-show-profile.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>
<body>
  <aside class="sidebar">
      <div class="logo">
          <a href="admin-data-of-user.php">
              <img src="../images/sikatugas-logo.png" alt="Logo Sikatugas" class="logo">
          </a>
      </div>
      <div class="menu">
          <div class="item"><a href="admin-data-of-user.php"><i class="fa-solid fa-users"></i>Data User</a></div>
          <div class="item"><a href="admin-user-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah User</a></div>
          <div class="item"><a href="admin-division-list.php"><i class="fa-solid fa-users"></i>Data Divisi</a></div>
          <div class="item"><a href="admin-division-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah Divisi</a></div>
          <div class="item"><a href="admin-profile.php" class="active"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
          <div class="item"><a href="logout-admin.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
      </div>
  </aside>

<header> 
  <div class="profile">
    <?php 
      $default_photo = "../images/photo-of-admin.jpg"; 
      $sql = "SELECT photo, name, email FROM admin WHERE id = 1"; 
      $result = $conn->query($sql);
      
      if ($result->num_rows > 0) {
          $admin = $result->fetch_assoc();
          $photo = $admin['photo'] ? "../images/" . $admin['photo'] : $default_photo;
      } else {
          $admin = ['name' => 'Nama tidak ditemukan', 'email' => 'Email tidak ditemukan'];
          $photo = $default_photo;;
      }
    ?>
     <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img" id="profile-img" onclick="toggleDetails()" 
       onerror="this.src='<?= $default_photo; ?>'">
        <div class="profile-details">
         <div class="profile-info">
            <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-sub-img" id="profile-img-preview" onclick="toggleDetails()" onerror="this.src='<?= $default_photo; ?>'">
            <div class="profile-text">
                <p id="name"><?= htmlspecialchars($admin['name']);?></p>  
                <p>Admin</p>  
                <p id="email"><?= htmlspecialchars($admin['email']);?></p>  
            </div>
            <div class="profile-actions">
              <button class="edit-btn">
                <a href="admin-profile.php"><i class="fas fa-user-edit"></i>  &nbsp;   Edit Profil</a>
              </button>
              <button class="logout-btn" onclick="logout()">
                <a href="logout-admin.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
              </button>
            </div>
      </div>
    </div>
</header>

<main>
<div class="profile-form-container">
    <h2>Edit Profil Admin </h2>
      <form action="admin-update-profile.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="profile-photo">Foto Profil :</label>
            <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img-preview" id="profile-img-edit" onclick="toggleDetails()" 
            onerror="this.src='<?= $default_photo; ?>'">
            <input type="file" id="profile-photo" name="photo" accept="image/*">
          </div>

          <div class="form-group">
            <label for="name">Nama :</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($admin['name']); ?>" required>
          </div>

          <input type="hidden" name="current_photo" value="<?= htmlspecialchars($admin['photo']); ?>">
          <button type="submit" class="submit btn">Simpan Perubahan</button>
          <a href="admin-data-of-user.php" class="btn back">Kembali ke Data User</a>
      </form>
  </div>
</main>