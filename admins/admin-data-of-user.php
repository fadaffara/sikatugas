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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Data User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin-data-of-user.css">
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
        <div class="item"><a href="admin-data-of-user.php" class="active"><i class="fa-solid fa-users"></i>Data User</a></div>
        <div class="item"><a href="admin-user-add.php"><i class="fas fa-solid fa-user-plus"></i>Tambah User</a></div>
        <div class="item"><a href="admin-division-list.php"><i class="fa-solid fa-users"></i>Data Divisi</a></div>
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
        <img src="<?= htmlspecialchars($photo); ?>" alt="Profile" class="profile-sub-img" 
           onerror="this.src='<?= $default_photo; ?>'">
            <div class="profile-text">
                <p id="name"><?= htmlspecialchars($admin['name']);?></p>  
                <p>Admin</p>  
                <p id="email"><?= htmlspecialchars($admin['email']);?></p>  
            </div>
            <div class="profile-actions">
              <button class="edit-btn">
                <a href="admin-profile.php"><i class="fas fa-user-edit"></i>  &nbsp; Edit Profil</a>
              </button>
              <button class="logout-btn" onclick="logout()">
                <a href="logout-admin.php"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a> 
              </button>
            </div>
      </div>
    </div>
</header>

<main>
  <h1>Data Semua User Sikatugas</h1> 
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Foto Profil</th>  
          <th>Username</th>
          <th>Email</th>
          <th>Name</th> 
          <th>Divisi</th>  
          <th>Tanggal Dibuat</th>
          <th>Tindakan</th>
        </tr>
      </thead>
      <tbody>
          <?php 
            $listUser = "SELECT users.*, divisi.nama_divisi FROM users LEFT JOIN divisi 
              ON users.divisi_id = divisi.kode_divisi";
            $result = mysqli_query($conn, $listUser);
              if (!$result) {
                die("Query gagal: " . mysqli_error($conn));
                }

            $rows = [];
              while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
                 }
          ?>

        <?php $i = 1; ?>
        <?php foreach ($rows as $row) : ?>
          <tr>
            <td class="number-center"><?= $i; ?></td>
            <td class="profile-photo">
              <?php 
                $user_photo = !empty($row["foto"]) ? "../images/" . $row["foto"] : "../images/photo-of-user.png";
              ?>
              <img src="<?= $user_photo; ?>" alt="Foto Profil" class="profile-photo">
            </td>
            <td><?= htmlspecialchars($row["username"]); ?></td>
            <td><?= htmlspecialchars($row["email"]); ?></td>
            <td><?= !empty($row["name"]) ? htmlspecialchars($row["name"]) : 'Kosong'; ?></td>
            <td><?= !empty($row["nama_divisi"]) ? htmlspecialchars($row["nama_divisi"]) : 'Tidak ada Divisi'; ?></td>
            <td><?= date('d-m-Y', strtotime($row["tanggal_dibuat"])); ?></td>
            <td>
                <button class="btn edit" onclick="window.location.href='admin-user-edit.php?id=<?= $row['id']; ?>'">Edit
                </button>
                <button class="btn delete" onclick="showConfirmDelete(<?= $row['id']; ?>)">Hapus</button>
            </td>
          </tr>
        <?php $i++; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
</main>

<div id="confirmModal">
  <div class="contentModal">
      <h3>Konfirmasi Penghapusan</h3>
      <p>Apakah Anda yakin ingin menghapus user ini?</p>
      <button onclick="deleteData()" class="btn btn-danger">Hapus</button>
      <button onclick="closeModal()" class="btn btn-secondary">Batal</button>
  </div>
</div>

<script>
  let deleteId = null;

  function showConfirmDelete(id) {
      deleteId = id; 
      document.getElementById('confirmModal').style.display = 'block'; 
  }

  function closeModal() {
      document.getElementById('confirmModal').style.display = 'none'; 
  }

  function deleteData() {
      if (deleteId) {
          window.location.href = 'admin-delete-users.php?id=' + deleteId;
         }
    }
</script>
</body>
</html>
