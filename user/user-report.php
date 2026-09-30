<?php 
require_once('C:/Users/ACER/Downloads/fpdf184/fpdf.php');
require_once ("../config.php");
session_start();
if (!isset($_SESSION['login_user'])) {
    header("Location: login-user.php");
    exit;
}

$email = $_SESSION['email_user'] ?? '';
$username = $_SESSION['username_user'] ?? '';
$name = $_SESSION['name_user'] ?? '';

$photo = $_SESSION['photo_user'] ?? '';
$default_photo = "../images/photo-of-user.png";
?>



<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User | Report Data Tugas </title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   <link rel="stylesheet" href="user-report.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="user-profile.js"></script>
   
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>
<body>


<aside class="sidebar">
     <div class="logo">
        <a href="user-dashboard.php">
            <img src="../images/sikatugas-logo.png" alt="Logo Sikatugas" class="logo">
        </a>
     </div>

    <div class="menu">
        <div class="item"><a href="user-dashboard.php"><i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="user-tasks.php"><i class="fas fa-solid fa-list-check"></i>List Tugas</a></div>
        <div class="item"><a href="user-report.php" class="active"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="user-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-user.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
        
    </div>
</aside>



 <header> 
   
   <div class="notification">
        <!-- <i class="fas fa-bell"></i> -->
    </div>

    <div id="notification-popup" class="notification-popup">
        <div class="notification-header">
            <span class="notification-title">Notifikasi</span>
            <button class="close-btn" onclick="closeNotification()">×</button>
        </div>
        <div id="notification-container" class="notification-container">
            <!-- Notifikasi akan dimasukkan di sini -->
        </div>
    </div>

  <div class="profile">
    <img 
        src="<?= htmlspecialchars($photo ?: $default_photo); ?>" 
        alt="Foto Profil <?= htmlspecialchars($username); ?>" 
        class="profile-img" 
        onclick="toggleDetails()" 
        onerror="this.src='<?= $default_photo; ?>'"
    >
    
    <div class="profile-details">
        <div class="profile-info">
            <img 
                src="<?= htmlspecialchars($photo ?: $default_photo); ?>" 
                alt="Foto Profil <?= htmlspecialchars($username); ?>" 
                class="profile-sub-img" 
                onerror="this.src='<?= $default_photo; ?>'"
            >
            <div class="profile-text">
          
                <p id="name"><?= !empty($name) ? htmlspecialchars($name) : (isset($unknown_name) ? "Unknown " . ++$unknown_name : "Unknown 1") ?></p>
              
                <p><?= !empty($username) ? htmlspecialchars($username) : (isset($unknown_username) ? "User " . ++$unknown_username : "User 1") ?></p>
      
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
 
  <div class="form-container">
    <form action="user-report-generate.php" method="POST" class="filter-form" id="report-form">
    <h1>Generate Report</h1>
      <div class="filter-item">
        <label for="filter">Filter:</label>
        <select id="filter" name="filter">
          <option value="start-date">Tanggal Mulai</option>
          <option value="deadline">Deadline</option>
        </select>
      </div>

      <div class="filter-item">
        <label for="start-range">Range Tanggal:</label>
        <input type="date" id="start-range" name="start-range">
        <span>Sampai</span>
        <input type="date" id="end-range" name="end-range">
      </div>

      <div class="filter-item">
        <label for="status">Status:</label>
        <select name="status" id="status">
          <option value="Semua">Semua</option>
          <option value="Menunggu Pengumpulan">Menunggu Pengumpulan</option>
          <option value="Menunggu Approval">Menunggu Approval</option>
          <option value="Butuh Revisi">Butuh Revisi</option>
          <option value="Selesai">Selesai</option>
        </select>
      </div>

      <div class="filter-item">
        <label for="prioritas">Prioritas:</label>
        <select name="prioritas" id="prioritas">
          <option value="Semua">Semua</option>
          <option value="Rendah">Rendah</option>
          <option value="Sedang">Sedang</option>
          <option value="Tinggi">Tinggi</option>
        </select>
      </div>

      <div class="button-container">
        <button type="submit" class="btn-report">Generate Report</button>
      </div>
    </form>
  </div>
</main>





 </body>
</html>