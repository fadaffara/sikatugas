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
if (isset($_GET['kode_divisi'])) {
      $kode_divisi = $_GET['kode_divisi'];
      $query = "SELECT * FROM divisi WHERE kode_divisi = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "s", $kode_divisi);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
  
      if (mysqli_num_rows($result) == 1) {
          $divisi = mysqli_fetch_assoc($result);
      } else {
          echo "Data tidak ditemukan.";
          exit;
      }
  } else {
      echo "Kode divisi tidak ditemukan.";
      exit;
  }
  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $nama_baru = $_POST['nama_divisi'];
      $kode_divisi_post = $_POST['kode_divisi'];
      $updateQuery = "UPDATE divisi SET nama_divisi = ? WHERE kode_divisi = ?";
      $stmt = mysqli_prepare($conn, $updateQuery);
      mysqli_stmt_bind_param($stmt, "ss", $nama_baru, $kode_divisi_post);

      $success = mysqli_stmt_execute($stmt);
      if ($success) {
          echo "<script>alert('Divisi berhasil diperbarui!'); window.location.href='admin-division-list.php';</script>";
          exit;
      } else {
          echo "Gagal update data: " . mysqli_error($conn);
      }
  }
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Edit Data Divisi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin-division-edit.css">
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
          <div class="item"><a href="admin-division-list.php" class="active"><i class="fa-solid fa-users"></i>Data Divisi</a></div>
          <div class="item"><a href="admin-division-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah Divisi</a></div>
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
            <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-sub-img" onerror="this.src='<?= $default_photo; ?>'">
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

<?php if (isset($divisi)) : ?>
<main>
  <h2>Edit Divisi: <?= htmlspecialchars($divisi['kode_divisi']); ?></h2>
  <form action="" method="POST">
  <input type="hidden" name="kode_divisi" value="<?= htmlspecialchars($divisi['kode_divisi']); ?>">
    <div class="form-group">
      <label for="nama_divisi">Nama Divisi</label>
      <input type="text" id="nama_divisi" name="nama_divisi" value="<?= htmlspecialchars($divisi['nama_divisi']); ?>" required>
    </div>
    <button type="submit" class="btn">Simpan Perubahan</button>
    <a href="admin-division-list.php" class="btns">Batal</a>
  </form>
</main>
<?php endif; ?>

</body>
</html>
