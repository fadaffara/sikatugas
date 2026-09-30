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


if (isset($_GET['id'])) {
    $id_user = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User tidak ditemukan.";
        exit;
    }
} else {
    echo "ID User tidak diberikan.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $divisi_id = $_POST['divisi_id'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, divisi_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $username, $name, $divisi_id, $id);

    if ($stmt->execute()) {
        header("Location: admin-data-of-user.php?pesan=berhasil");
        echo "Profil User Berhasil Diperbarui";
        exit;
    } else {
        echo "Gagal update user: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Edit Data User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin-user-edit.css">
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
</header><?php if (isset($user)) : ?>
<main>
  <h2>Edit Data User</h2>
  <form action="admin-user-edit.php?id=<?= $user['id']; ?>" method="POST">
    <input type="hidden" name="id" value="<?= $user['id']; ?>">

    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
    </div>

    <div class="form-group">
      <label for="name">Nama Lengkap</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
    </div>

    <div class="form-group">
      <label for="divisi_id">Divisi</label>
      <select name="divisi_id" id="divisi_id" required>
        <option value="">-- Pilih Divisi --</option>
        <?php 
          $divisi_result = $conn->query("SELECT * FROM divisi");
          while ($d = $divisi_result->fetch_assoc()) {
              $selected = $user['divisi_id'] == $d['kode_divisi'] ? 'selected' : '';
              echo "<option value='{$d['kode_divisi']}' $selected>{$d['nama_divisi']}</option>";
          }
        ?>
      </select>
    </div>

    <button type="submit" class="btn">Simpan Perubahan</button>
    <a href="admin-data-of-user.php" class="btns">Batal</a>
  </form>
</main>
<?php endif; ?>
</body>
</html>