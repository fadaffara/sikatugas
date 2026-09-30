<?php 
require_once ("../config.php");
session_start(); 
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php"); 
    exit;
}
include_once("manager-priority-update.php");

$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) && $_GET['perPage'] > 0 ? (int)$_GET['perPage'] : 7; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filters = [];

if (!empty($_GET['username'])) {
    $filters[] = "ditugaskan = '" . mysqli_real_escape_string($conn, $_GET['username']) . "'";
}
if (!empty($_GET['status'])) {
    $filters[] = "status = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'";
}
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
  $startDate = $_GET['start_date'];
  $endDate = $_GET['end_date'];
  
  
  if (strtotime($startDate) > strtotime($endDate)) {
      $temp = $startDate;
      $startDate = $endDate;
      $endDate = $temp;
  }

  $filters[] = "deadline BETWEEN '" . mysqli_real_escape_string($conn, $startDate) . "' AND '" . mysqli_real_escape_string($conn, $endDate) . "'";
}


if (!empty($_GET['prioritas'])) {
  $filters[] = "prioritas = '" . mysqli_real_escape_string($conn, $_GET['prioritas']) . "'";
}

if (!empty($search)) {
  $searchEscaped = mysqli_real_escape_string($conn, $search);
  $filters[] = "(tasks.nama_tugas LIKE '%$searchEscaped%' 
                OR tasks.ditugaskan LIKE '%$searchEscaped%' 
                OR tasks.status LIKE '%$searchEscaped%' 
                OR tasks.prioritas LIKE '%$searchEscaped%' 
                OR tasks.deadline LIKE '%$searchEscaped%' 
                OR users.name LIKE '%$searchEscaped%')";
}

$whereClause = "";
if (count($filters) > 0) {
    $whereClause = " WHERE " . implode(" AND ", $filters);
}

$totalQuery = "SELECT COUNT(*) AS total 
               FROM tasks 
               JOIN users ON tasks.ditugaskan = users.username" 
               . $whereClause;

$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRows = $totalRow['total'];


$query = "SELECT tasks.*, users.name, users.foto, users.username 
          FROM tasks
          JOIN users ON tasks.ditugaskan = users.username" 
          . $whereClause . " LIMIT ?, ?";

$stmt = $conn->prepare($query);
$params = []; 
$types = "";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii"; 
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}



$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;
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

function buildQuery($paramsToOverride = []) {
  $query = $_GET;
  foreach ($paramsToOverride as $key => $value) {
      $query[$key] = $value;
  }
  return http_build_query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manager | Data Tugas User</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   <link rel="stylesheet" href="manager-tasks.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
        <div class="item"><a href="manager-tasks.php" class="active"><i class="fas fa-solid fa-list-check"></i>Data Tugas User</a></div>
        <div class="item"><a href="manager-insert.php"><i class="fas fa-solid fa-plus"></i>Tambah Tugas </a></div>
        <div class="item"><a href="manager-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="manager-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-manager.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
     </div>
 </aside>


 <header> 
 <h1>Data Tugas User</h1>
 <div class="notification">
        <!-- <i class="fas fa-bell"></i> -->
    </div>

    <div id="notification-popup" class="notification-popup">
        <div class="notification-header">
            <span class="notification-title">Notifikasi</span>
            <button class="close-btn" onclick="closeNotification()">×</button>
        </div>
        <div id="notification-container" class="notification-container">
       
        </div>
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
   
    <div class="filter-search-bar">
    <form action="manager-tasks.php" method="GET" class="search-form">
        
        <input type="hidden" name="username" value="<?= htmlspecialchars($_GET['username'] ?? ''); ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status'] ?? ''); ?>">
        <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? ''); ?>">
        <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? ''); ?>">
        <input type="hidden" name="prioritas" value="<?= htmlspecialchars($_GET['prioritas'] ?? ''); ?>">

        <div class="search-container">
            <input type="text" name="search" placeholder="Cari tugas..." value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" class="search-input">
            <button type="submit" class="search-btn">
                <i class="fa-solid fa-search"></i>
            </button>
        </div>
    </form>

    <div class="button-container">
        <a href="manager-report.php" class="btn btn-report">
            <i class="fas fa-file-alt"></i> &nbsp; &nbsp; Report
        </a>
        <a href="manager-insert.php" class="btn btn-tambah-tugas">
            <i class="fas fa-plus"></i> &nbsp; &nbsp; Tambah Tugas Baru
        </a>

        <button id="openFilterModal" class="btn btn-filter"><i class="fa-solid fa-filter"></i>&nbsp; &nbsp;Filter</button>
    </div>
</div>



<div id="filterModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Filter Data</h2>
    <form action="manager-tasks.php" method="GET" class="filter-form" id="filterForm">
      <label for="username">Username:</label>
      <select name="username" id="username">
        <option value="">Semua</option>
        <?php
          $userQuery = "SELECT DISTINCT ditugaskan FROM tasks";
          $userResult = mysqli_query($conn, $userQuery);
          while ($user = mysqli_fetch_assoc($userResult)) {
            echo '<option value="' . htmlspecialchars($user['ditugaskan']) . '" ' . ($_GET['username'] == $user['ditugaskan'] ? 'selected' : '') . '>' . htmlspecialchars($user['ditugaskan']) . '</option>';
          }
        ?>
      </select>

      <label for="status">Status:</label>
      <select name="status" id="status">
        <option value="">Semua</option>
    
        <option value="menunggu pengumpulan" <?= ($_GET['status'] ?? '') == 'menunggu pengumpulan' ? 'selected' : ''; ?>>Menunggu Pengumpulan</option>
        <option value="menunggu approval" <?= ($_GET['status'] ?? '') == 'menunggu approval' ? 'selected' : ''; ?>>Menunggu Approval</option>
        <option value="butuh revisi" <?= ($_GET['status'] ?? '') == 'butuh revisi' ? 'selected' : ''; ?>>Butuh Revisi</option>
        <option value="selesai" <?= ($_GET['status'] ?? '') == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
      </select>

      <label for="prioritas">Prioritas:</label>
      <select name="prioritas" id="prioritas">
        <option value="">Semua</option>
        <option value="Rendah" <?= ($_GET['prioritas'] ?? '') == 'Rendah' ? 'selected' : ''; ?>>Rendah</option>
        <option value="Sedang" <?= ($_GET['prioritas'] ?? '') == 'Sedang' ? 'selected' : ''; ?>>Sedang</option>
        <option value="Tinggi" <?= ($_GET['prioritas'] ?? '') == 'Tinggi' ? 'selected' : ''; ?>>Tinggi</option>
      </select>

      <div class="deadline-filter">
        <label for="deadline">Deadline:</label>
        <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? ''; ?>" />
        <span>sampai</span>
        <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? ''; ?>" />
      </div>  

      <div class="pagination-section">
        <label for="perPage">Menampilkan</label>
        <select name="perPage" id="perPage">
            <option value="1" <?= $perPage == 1 ? 'selected' : ''; ?>>1</option>
            <option value="2" <?= $perPage == 2 ? 'selected' : ''; ?>>2</option>
            <option value="3" <?= $perPage == 3 ? 'selected' : ''; ?>>3</option>
            <option value="4" <?= $perPage == 4 ? 'selected' : ''; ?>>4</option>
            <option value="5" <?= $perPage == 5 ? 'selected' : ''; ?>>5</option>
            <option value="6" <?= $perPage == 6 ? 'selected' : ''; ?>>6</option>
            <option value="7" <?= $perPage == 7 ? 'selected' : ''; ?>>7</option>
            <option value="8" <?= $perPage == 8 ? 'selected' : ''; ?>>8</option>
            <option value="9" <?= $perPage == 9 ? 'selected' : ''; ?>>9</option>
            <option value="10" <?= $perPage == 10 ? 'selected' : ''; ?>>10</option>
        </select>
        <label for="perPage"> &nbsp data per halaman</label>
    </div>


      <div class="modal-footer">
        <button type="button" id="cancelFilter" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Terapkan</button>
      </div>
    </form>
  </div>
</div>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Tugas</th>
      <th>Ditugaskan</th>
      <th>Tanggal Mulai</th>
      <th>Deadline</th>
      <th>Status</th>
      <th>Prioritas</th>
      <th>Tindakan</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($rows) == 0) : ?>
      <tr>
        <td colspan="8" style="text-align: center; font-weight: bold;">
          <p>Semua tugas telah selesai diproses</p>
        </td>
      </tr>
    <?php else : ?>
      <?php $i = $offset + 1; ?>
      <?php foreach ($rows as $row) : ?>
        <tr>
          <td class="number-center"><?= $i; ?></td>
          <td><?= htmlspecialchars($row["nama_tugas"]); ?></td>
          <td>
         
            <div class="user-info">
              <img src="../images/<?= htmlspecialchars($row['foto']) ?: 'default-profile.jpg'; ?>" alt="Foto Profil" width="40" height="40" style="border-radius: 50%;">
              <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($row["name"]); ?></div>
                <div class="user-username"><?= htmlspecialchars($row["username"]); ?></div>
              </div>
            </div>
          </td>
          <td><?= date('d-m-Y', strtotime($row["tanggal_mulai"])); ?></td>
          <td><?= date('d-m-Y', strtotime($row["deadline"])); ?></td>
          <td class="status"><?= htmlspecialchars($row["status"]); ?></td>
          <td class="prioritas"><?= htmlspecialchars($row["prioritas"]); ?></td>
          <td>
            <button class="btn view" onclick="openDetailModal(<?= $row['id']; ?>)">
              <i class="fa-solid fa-eye"></i>
            </button>
            <button 
              class="btn edit" 
              <?= (strtotime($row['deadline']) < strtotime(date('Y-m-d')) && $row['status'] !== 'Selesai') ? 'disabled' : ''; ?> 
              onclick="window.location.href='admin-edit.php?id=<?= $row['id']; ?>'">
              <i class="fas fa-pencil-alt"></i>
            </button>
            <button class="btn delete" onclick="showConfirmDelete(<?= $row['id']; ?>)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>
        <?php $i++; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>


<div class="page-navigation">
    <?php if ($totalRows > 0): ?>
      <a href="?<?= buildQuery(['page' => 1, 'perPage' => $perPage]); ?>" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>"><i class="fa-duotone fa-solid fa-backward-fast"></i></a>
    <a href="?<?= buildQuery(['page' => $page - 1, 'perPage' => $perPage]); ?>" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>"><i class="fa-solid fa-backward-step"></i></a>
    <span class="current-page">Halaman <?= $page; ?> dari <?= $totalPages; ?></span>
    <a href="?<?= buildQuery(['page' => $page + 1, 'perPage' => $perPage]); ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>"><i class="fa-solid fa-forward-step"></i></a>
    <a href="?<?= buildQuery(['page' => $totalPages, 'perPage' => $perPage]); ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>"><i class="fa-solid fa-forward-fast"></i></a>
    <?php else: ?>
       
        <a href="#" class="page-link disabled"><i class="fa-duotone fa-solid fa-backward-fast"></i></a>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-backward-step"></i></a>
           <span class="current-page">Halaman 0 dari 0</span>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-forward-step"></i></a>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-forward-fast"></i></a>
    <?php endif; ?>
</div>


  </main>

<div id="confirmModal">
  <div class="contentModal">
    <h3>Konfirmasi Penghapusan</h3>
    <p>Apakah Anda yakin ingin menghapus tugas ini?</p>
    <button onclick="deleteData()" class="btn btn-danger">Hapus</button>
    <button onclick="closeConfirmModal()" class="btn btn-secondary">Batal</button>
  </div>
</div>


<div id="taskDetailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTasksDetailsModal()">&times;</span>
        <div id="taskDetailContent"></div>  
    </div>
</div>


<script src="manager-tasks.js"></script>
<script src="manager-show-profile.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {

  $('td.prioritas').each(function() {
    var prioritas = $(this).text().trim();
    var colors = {
      'Rendah': {'bg': '#cce7ff', 'color': '#003366'},
      'Sedang': {'bg': '#fff9c4', 'color': '#b28900'},
      'Tinggi': {'bg': '#ff7043', 'color': 'white'}
    };
    var color = colors[prioritas];
    if (color) {
      $(this).css({
        'background-color': color.bg,
        'color': color.color,
        'font-weight': 'bold',
        'padding': '5px 10px',
        'transition': 'background-color 0.3s ease'
      });
    }
  });


  $('td.status').each(function() {
    var status = $(this).text().trim();
    var statusColors = {
      'Menunggu Pengumpulan': {'bg': '#80d0ff', 'color': '#0066cc'},
      'Menunggu Approval': {'bg': '#ffcc80', 'color': '#ff6600'},
      'Butuh Revisi': {'bg': '#ffee58', 'color': '#f57f17'},
      'Selesai': {'bg': '#81c784', 'color': 'white'}
    };
    var statusColor = statusColors[status];
    if (statusColor) {
      $(this).css({
        'background-color': statusColor.bg,
        'color': statusColor.color,
        'font-weight': 'bold',
        'padding': '5px 10px',
        'transition': 'background-color 0.3s ease'
      });
    }
  });


  var today = new Date();
  today.setHours(0, 0, 0, 0); 

  $('table tbody tr').each(function() {
    var deadlineText = $(this).find('td:nth-child(5)').text().trim();
    var deadlineParts = deadlineText.split('-');
    var deadlineDate = new Date(deadlineParts[2], deadlineParts[1] - 1, deadlineParts[0]);

    if (deadlineDate.getTime() === today.getTime()) {
      $(this).find('td:nth-child(5)').addClass('deadline-today');
    } else if (deadlineDate < today) {
      $(this).find('td:nth-child(5)').addClass('deadline-passed');
    } else {
      $(this).find('td:nth-child(5)').addClass('deadline-upcoming');
    }
  });
});


  $('table tbody tr').each(function() {
    $(this).find('td:nth-child(4)').css({
      'background-color': '#e6e6fa',
      'color': '#4b0082',
      'font-weight': 'bold',
      'padding': '5px 10px',
      'transition': 'background-color 0.3s ease'
    });
  });

</script>
</body>
</html>














