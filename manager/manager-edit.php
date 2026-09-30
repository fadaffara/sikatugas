<?php 
session_start();
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php");
    exit;
}
require_once ("../config.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $query = "SELECT * FROM tasks WHERE id = $id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    } else {
        echo "Data tugas tidak ditemukan.";
        exit;
    }
    $user_query = mysqli_query($conn, "SELECT * FROM users");
    if (!$user_query) {
        die("Query gagal: " . mysqli_error($conn));
    }
} else {
    echo "ID tidak ditemukan.";
    exit;
}

// Admin photo setup
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

// Helper untuk atribut readonly/disabled
function setAttribute($condition, $attribute) {
    return $condition ? $attribute : '';
}

// Cek apakah statusnya selesai
$current_date = date('Y-m-d');
$status_selesai = $data['status'] === 'Selesai';
$is_deadline_passed = strtotime($data['deadline']) < strtotime($current_date);
$is_deadline_today = strtotime($data['deadline']) == strtotime($current_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manager | Edit Data Tugas User</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   <link rel="stylesheet" href="manager-edit.css">
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
        <div class="item"><a href="manager-user-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah User</a></div>
        <div class="item"><a href="manager-admin.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>
</aside>


<header> 
 <h1>Edit Data Tugas User</h1>
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
                 <a href="logout.manager.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
             </button>
         </div>
       </div>
   </div>
  
 </header>
 <div class="form-container">
    <?php if ($is_deadline_passed): ?>
        <p style="color: red; font-weight: bold;">
            ⚠️  Deadline tugas ini sudah lewat. Anda masih bisa mengedit tugas, namun pastikan untuk mengomunikasikan perubahan ini kepada pihak terkait.".
        </p>
    <?php elseif ($is_deadline_today): ?>
        <p style="color: orange; font-weight: bold;">
            ⚠️ Deadline tugas ini adalah hari ini. Pastikan tugas ini selesai tepat waktu.
        </p>
    <?php endif; ?>

    <form action="manager-update.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">
    <input type="hidden" name="status" value="<?= htmlspecialchars($data['status']); ?>">

    <label>Nama Tugas:</label>
    <input type="text" name="nama_tugas" value="<?= $data['nama_tugas'] ?>" <?= $status_selesai ? 'readonly' : '' ?> required>

    <label>Detail Tugas:</label>
    <textarea name="detail_tugas" <?= $status_selesai ? 'readonly' : '' ?> required><?= htmlspecialchars($data['detail_tugas']) ?></textarea>

    <label>Ditugaskan:</label>
    <select name="ditugaskan" disabled required>
        <?php while ($user = mysqli_fetch_assoc($user_query)): ?>
            <option value="<?= $user['username'] ?>" 
                <?= ($user['username'] == $data['ditugaskan']) ? 'selected' : '' ?>>
                <?= $user['username'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Tanggal Mulai:</label>
    <input type="date" value="<?= date('Y-m-d', strtotime($data['tanggal_mulai'])) ?>" readonly>

    <label>Deadline:</label>
    <input type="date" name="deadline" min="<?= date('Y-m-d'); ?>" value="<?= date('Y-m-d', strtotime($data['deadline'])) ?>" 
   style="<?= $is_deadline_passed ? 'border: 2px solid red;' : ($is_deadline_today ? 'border: 2px solid orange;' : '') ?>" 
   <?= $is_deadline_passed ? 'readonly' : '' ?> required>
   
    <label>Prioritas:</label>
    <select name="prioritas" disabled required>
        <option value="Rendah" <?= ($data['prioritas'] == 'Rendah') ? 'selected' : '' ?>>Rendah</option>
        <option value="Sedang" <?= ($data['prioritas'] == 'Sedang') ? 'selected' : '' ?>>Sedang</option>
        <option value="Tinggi" <?= ($data['prioritas'] == 'Tinggi') ? 'selected' : '' ?>>Tinggi</option>
    </select>

    <label>File Admin:</label>
    <p>File Saat Ini: <?= $data['file_admin'] ? $data['file_admin'] : 'Tidak ada file'; ?></p>
    <input type="hidden" name="file_lama" value="<?= $data['file_admin']; ?>">
    <input type="file" name="file_admin" <?= $status_selesai ? 'disabled' : '' ?>>

    
    <?php if (!empty($data['file_admin'])): ?>
        <label>
            <input type="checkbox" name="hapus_file" value="1" <?= $status_selesai ? 'disabled' : '' ?>>
            Hapus file yang ada
        </label>
    <?php endif; ?> 

    <input type="hidden" name="ditugaskan" value="<?= htmlspecialchars($data['ditugaskan']) ?>">
    <input type="hidden" name="prioritas" value="<?= htmlspecialchars($data['prioritas']) ?>">

    <button type="submit" <?= $status_selesai ? 'disabled' : '' ?>>Update</button>
</form>
</div>



</div>
</body>
</html>