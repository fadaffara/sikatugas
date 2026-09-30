<?php 
require_once ("../config.php");
session_start(); 
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-admin.php"); 
    exit;
}
$default_photo = "../images/photo-of-admin.jpg"; 
$sql = "SELECT photo, name, email FROM admin WHERE id = 1"; 
$result = $conn->query($sql); 
if ($result->num_rows > 0) {
          $admin = $result->fetch_assoc();
          $photo = $admin['photo'] ? "../images/" . $admin['photo'] : $default_photo;
     } else {
          $admin = ['name' => 'Nama tidak ditemukan', 'email' => 'Email tidak ditemukan'];
          $photo = $default_photo;
     }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_divisi = $_POST['kode_divisi'];
    $nama_divisi = $_POST['nama_divisi'];
    $jumlah_anggota = $_POST['jumlah_anggota'];
    $created_at = date('Y-m-d H:i:s');
    $cek = mysqli_query($conn, "SELECT * FROM divisi WHERE kode_divisi = '$kode_divisi'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Kode divisi sudah digunakan. Silakan gunakan kode lain.');
                window.history.back();
              </script>";
    } else {
        $query = "INSERT INTO divisi (kode_divisi, nama_divisi, jumlah_anggota, created_at)
                  VALUES ('$kode_divisi', '$nama_divisi', '$jumlah_anggota', '$created_at')";
        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Divisi berhasil ditambahkan!');
                    window.location.href = 'admin-division-add.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Terjadi kesalahan saat menambahkan divisi.');
                    window.history.back();
                  </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Tambah Data Divisi Baru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin-division-add.css">
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
        <div class="item"><a href="admin-division-add.php" class="active"><i class="fas fa-solid fa-user-plus"></i>
        Tambah Divisi</a></div>
        <div class="item"><a href="admin-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-admin.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>
</aside>

<header> 
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
                        <button class="edit-btn"><a href="admin-profile.php"><i class="fas fa-user-edit"></i>  &nbsp;   
                        Edit Profil</a>
                        </button>
                        <button class="logout-btn" onclick="logout()">
                        <a href="logout-admin.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
                        </button>
                    </div>
        </div>
    </div>
</header>
<main>
<h2>Form Tambah Divisi Baru</h2>
    <form id="divisiForm" action="admin-division-add.php" method="POST">
        <div class="form-group">
            <label for="kode_divisi">Kode Divisi</label>
            <input type="text" id="kode_divisi" name="kode_divisi" placeholder="Masukkan kode divisi" required>
        </div>
        <div class="form-group">
            <label for="nama_divisi">Nama Divisi</label>
            <input type="text" id="nama_divisi" name="nama_divisi" placeholder="Masukkan nama divisi" required>
        </div>
        <div class="form-group">
        <input type="hidden" id="jumlah_anggota" name="jumlah_anggota" value="0">
        </div>
        <button type="submit" class="btn">Tambah Divisi</button>
    </form>
</main>

</body>
</html>