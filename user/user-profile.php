<?php 
require_once ("../config.php");
session_start(); 

if (!isset($_SESSION['login_admin'])) {
    header("Location: login-admin.php"); 
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login-admin.php");
    exit;
}

$default_photo = "../images/photo-of-user.png";

$sql = "SELECT name, username, email, foto FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
   
    $user = $result->fetch_assoc();
    $name = !empty($user['name']) ? $user['name'] : 'Nama tidak ditemukan';
    $username = !empty($user['username']) ? $user['username'] : 'Username tidak ditemukan';
    $email = !empty($user['email']) ? $user['email'] : 'Email tidak ditemukan';

   
    $photo_path = "../images/" . $user['foto'];
    if (!empty($user['foto']) && file_exists($photo_path)) {
        $photo = $photo_path;
    } else {
        $photo = $default_photo;
    }
} else {
    // Data user tidak ditemukan
    $photo = $default_photo;
    $name = 'Nama tidak ditemukan';
    $username = 'Username tidak ditemukan';
    $email = 'Email tidak ditemukan';
}

// Simpan data ke session untuk efisiensi
$_SESSION['photo_user'] = $photo;
$_SESSION['name_user'] = $name;
$_SESSION['username_user'] = $username;
$_SESSION['email_user'] = $email;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User | Profil</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="user-profile.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="user-profile.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>

<body>

<aside class="sidebar">
    <div class="logo">
       <img src="../images/sikatugas-logo.png" alt="Logo Sikatugas" class="logo">
    </div>
   
    <div class="menu">
    <div class="item"><a href="user-dashboard.php"><i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="user-tasks.php"><i class="fas fa-solid fa-list-check"></i>List Tugas</a></div>
        <div class="item"><a href="user-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="user-profile.php" class="active"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-user.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
        
    </div>
</aside>


<header> 


  <div class="notification">
    <i class="fas fa-bell"></i>
  </div>

  <div class="profile">
        <?php 
            $default_photo = "../images/photo-of-user.png"; 
            $sql = "SELECT foto, name, username FROM users WHERE id = $user_id"; 
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user= $result->fetch_assoc();
                $photo = $user['foto'] ? "../images/" . $user['foto'] : $default_photo;
            } else {
                $user = ['name' => 'Nama tidak ditemukan', 'email' => 'Email tidak ditemukan'];
                $photo = $default_photo;
            }
          ?>
    <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img" id="profile-img" onclick="toggleDetails()" 
       onerror="this.src='<?= $default_photo; ?>'">
    <div class="profile-details">
      <div class="profile-info">
        <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-sub-img" id="profile-img-preview" onclick="toggleDetails()" 
        onerror="this.src='<?= $default_photo; ?>'">
            <div class="profile-text">
                <p id="name"><?= htmlspecialchars($name); ?></p>  
                <p id="username"><?= htmlspecialchars($username); ?></p>  
                <p id="email"><?= htmlspecialchars($email); ?></p>  
            </div>
            
            <div class="profile-actions">
            <button class="edit-btn">
               <a href="user-profile.php"><i class="fas fa-user-edit"></i>  &nbsp;   Edit Profil</a>
            </button>
            <button class="logout-btn" onclick="logout()">
               <a href="logout-user.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
            </button>
            </div>
        
      </div>
    </div>
  </div>
</header>


<main>
<div class="profile-form-container">
    <h2>Edit Profil User</h2>
    <form action="user-update-profile.php" method="POST" enctype="multipart/form-data">
    
    
      <div class="form-group">
        <label for="profile-photo">Foto Profil</label>
 
        <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img-preview" id="profile-img-edit" 
        onerror="this.src='<?= $default_photo; ?>'">
        <input type="file" id="profile-photo" name="photo" accept="image/*">
      </div>

      <!-- Username -->
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
      </div>

      <!-- Nama -->
      <div class="form-group">
        <label for="name">Nama</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name); ?>" required>
      </div>

 
      <input type="hidden" name="current_photo" value="<?= htmlspecialchars($user['foto']); ?>">

      <button type="submit" class="submit btn">Simpan Perubahan</button>
      <a href="user-profile.php" class="btn back">Kembali ke Profil</a>

    </form>
</div>
</main>

</body>
</html>
