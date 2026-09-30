<?php 
require_once ("../config.php");

session_start(); 

if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php"); 
    exit;
}

if (isset($_GET['id'])) {
  $userId = $_GET['id'];
  $query = "SELECT * FROM users WHERE id = '$userId'";
  $result = mysqli_query($conn, $query);
  $user = mysqli_fetch_assoc($result);
}

if (isset($_POST['submit'])) {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  // Update hanya name dan username
  $query = "UPDATE users SET name='$name', username='$username' WHERE id='$userId'";

  if (mysqli_query($conn, $query)) {
      header("Location: manager-user-list.php");
      echo "User berhasil diupdate.";
  } else {
      echo "Error: " . mysqli_error($conn);
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
  <title>Manager | Edit Data User</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="manager-user-edit.css">
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
        <div class="item"><a href="" class="active"><i class="fa-solid fa-users"></i>Data User</a></div>
        <div class="item"><a href="manager-user-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah User</a></div>
        <div class="item"><a href="logout-manager.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>
</aside>

<header> 
 

  <div class="notification">
    <i class="fas fa-bell"></i>
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

<main class="container">
    <h2>Edit Data User</h2>
    <form action="manager-user-edit.php?id=<?= $userId; ?>" method="POST">
        <label for="name">Name: </label>
        <input type="text" id="name" name="name" value="<?= isset($user) ? htmlspecialchars($user['name']) : ''; ?>" required>

        <label for="username">Username: </label>
        <input type="text" id="username" name="username" value="<?= isset($user) ? htmlspecialchars($user['username']) : ''; ?>" required>

        <button type="submit" name="submit">Update</button>
    </form>
</main>

</body>
</html>
