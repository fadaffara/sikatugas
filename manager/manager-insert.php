<?php
require_once("../config.php");
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

if (isset($_POST['tambah'])) {
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $ditugaskan = mysqli_real_escape_string($conn, $_POST['ditugaskan']);
    $tanggal_mulai = date("Y-m-d H:i:s"); // contoh: 2025-04-17 10:12:45
    $deadline = $_POST['deadline'];
    $detail_tugas = mysqli_real_escape_string($conn, $_POST['detail_tugas']);

    // Deadline set ke jam 23:59:59
    $deadline_date = new DateTime($deadline);
    $deadline_date->setTime(23, 59, 59);

        // Waktu saat ini
        // Menentukan prioritas berdasarkan deadline
    $today = new DateTime();
    $deadline_only_date = clone $deadline_date;
    $deadline_only_date->setTime(0, 0, 0);

    $interval = $today->diff($deadline_only_date)->days;
    $is_future = $deadline_only_date >= $today;

    if ($is_future && $interval <= 1) {
        // Deadline hari ini atau besok
        $prioritas = 'Tinggi';
    } elseif ($is_future && $interval >= 2) {
        // Deadline 2 hari ke atas (berarti minimal 3 hari ke depan dari hari ini)
        $prioritas = !empty($_FILES['file_admin']['name']) ? 'Sedang' : 'Rendah';
    } else {
        // Deadline sudah lewat
        $prioritas = 'Tinggi';
    }


    // Proses upload file (opsional)
    $file_admin = '';
    $upload_dir = "uploads/";
    $allowed_extensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'];
    $max_file_size = 5 * 1024 * 1024;

    if (isset($_FILES['file_admin']) && $_FILES['file_admin']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['file_admin']['tmp_name'];
        $original_name = $_FILES['file_admin']['name'];
        $target_file = $upload_dir . basename($original_name);
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($_FILES['file_admin']['size'] > $max_file_size) {
            die("Error: Ukuran file tidak boleh lebih dari 5 MB.");
        }

        if (!in_array($file_ext, $allowed_extensions)) {
            die("Error: Hanya file berformat arsip (ZIP, RAR, 7Z, TAR, GZ, BZ2, XZ) yang diizinkan.");
        }

        if (file_exists($target_file)) {
            $filename = pathinfo($original_name, PATHINFO_FILENAME); 
            $extension = pathinfo($original_name, PATHINFO_EXTENSION); 
            $counter = 1;

            do {
                $new_file_name = $filename . "_" . $counter . "." . $extension;
                $target_file = $upload_dir . $new_file_name;
                $counter++;
            } while (file_exists($target_file));
            $file_admin = $new_file_name;
        } else {
            $file_admin = $original_name;
        }

        if (!move_uploaded_file($tmp_name, $target_file)) {
            die("Gagal upload file.");
        }
    }

    // Query untuk insert data ke database
    $sql = "INSERT INTO tasks (nama_tugas, ditugaskan, tanggal_mulai, deadline, detail_tugas, status, file_pengumpulan, file_admin, prioritas) 
            VALUES ('$nama_tugas', '$ditugaskan', '$tanggal_mulai', '".$deadline_date->format('Y-m-d H:i:s')."', '$detail_tugas', 'Menunggu Pengumpulan', '', '$file_admin', '$prioritas')";

    if (mysqli_query($conn, $sql)) {
        header("Location: manager-tasks.php");
        exit();
    } else {
        die("Error SQL: " . mysqli_error($conn));
    }
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager | Tambah Tugas User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="manager-insert.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nerko+One&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
<aside class="sidebar">
    <div class="logo">
        <a href="manager-dashboard.php">
            <img src="../images/sikatugas-logo.png" alt="Logo Sikatugas" class="logo">
        </a>
    </div>
    <div class="menu">
        <div class="item"><a href="manager-dashboard.php">
            <i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="manager-tasks.php">
            <i class="fas fa-solid fa-list-check"></i>Data Tugas User</a></div>
        <div class="item"><a href="manager-insert.php" class="active">
            <i class="fas fa-solid fa-plus"></i>Tambah Tugas </a></div>
        
       
        <div class="item"><a href="manager-report.php">
            <i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="manager-profile.php">
            <i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-manager.php">
            <i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>
</aside>

<header> 
  <div class="notification">
    <!-- <i class="fas fa-bell"></i> -->
  </div>
  <div class="profile">
    <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-img" 
          onclick="toggleDetails()" onerror="this.src='<?= $default_photo; ?>'">
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


<main class="container">
    <h2>Form Input Tugas User</h2>
    <form action="manager-insert.php" method="POST" enctype="multipart/form-data">
        <label for="task_title">Judul Tugas : </label>
        <input type="text" id="task_title" name="nama_tugas" placeholder="Masukkan judul tugas" required>

        <label for="task_details">Detail Tugas:</label>
        <textarea id="task_details" name="detail_tugas" maxlength="300" rows="4" placeholder="Masukkan detail tugas" required></textarea>

        <label for="assigned_to">Ditugaskan Untuk : </label>
        <select id="assigned_to" name="ditugaskan" required>
            <option value="">-- Pilih User --</option>
            <?php
                $result = mysqli_query($conn, "SELECT username, name FROM users");
                while ($row = mysqli_fetch_assoc($result)) {
                    $username = htmlspecialchars($row['username']);
                    $name = htmlspecialchars($row['name']);
                    echo "<option value='$username'>$username ($name)</option>";
                }
            ?>

        </select>

        <label for="deadline">Deadline : </label>
        <input type="date" id="deadline" name="deadline" min="<?= date('Y-m-d'); ?>" required>

        <label for="file_admin">Upload File:</label>
        <input type="file" id="file_admin" name="file_admin">

        <button type="submit" name="tambah" class="btn simpan">Simpan Tugas</button>
    </form>
</main>





<script src="manager-show-profile.js"></script>
</body>
</html>
