<?php 
require_once('C:/Users/ACER/Downloads/fpdf184/fpdf.php');
require_once ("../config.php");
session_start(); 
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php"); 
    exit;
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
   <title>Manager | Data Tugas User</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   <link rel="stylesheet" href="manager-report.css">
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
        <div class="item"><a href="manager-tasks.php" ><i class="fas fa-solid fa-list-check"></i>Data Tugas User</a></div>
        <div class="item"><a href="manager-insert.php"><i class="fas fa-solid fa-plus"></i>Tambah Tugas </a></div>
    
        <div class="item"><a href="manager-report.php" class="active"><i class="fas fa-file-alt"></i>Report</a></div>
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
              <p>Manager</p>  
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
   
    <div class="form-container">
    <form action="manager-report-generate.php" method="POST" class="filter-form" id="report-form">
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
            <select id="status" name="status">
                <option value="">Semua</option>
                <option value="Menunggu Pengumpulan">Menunggu Pengumpulan</option>
                <option value="Menunggu Approval">Menunggu Approval</option>
                <option value="Butuh Revisi">Butuh Revisi</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="prioritas">Prioritas:</label>
            <select id="prioritas" name="prioritas">
                <option value="">Semua</option>
                <option value="Rendah">Rendah</option>
                <option value="Sedang">Sedang</option>
                <option value="Tinggi">Tinggi</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="ditugaskan">Ditugaskan:</label>
            <select id="ditugaskan" name="ditugaskan">
                <option value="">Semua</option>
                <?php
                // Fetch usernames from the users table
                $sql = "SELECT username FROM users";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['username'] . "'>" . $row['username'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="button-container">
            <button type="submit" class="btn-report">Generate Report</button>
        </div>
    </form>
</div>


   
        <div id="pdf-preview" style="display: none;">
            <h2>Report Preview</h2>
            <embed id="pdf-embed" type="application/pdf" width="100%" height="500px" />
        </div>
    </div>
</main>





 </body>
</html>