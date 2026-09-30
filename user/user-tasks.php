<?php
include("../config.php");
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
$user_id = $_SESSION['user_id'];


$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) && $_GET['perPage'] > 0 ? (int)$_GET['perPage'] : 7;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = $_GET['status'] ?? '';
$prioritas = $_GET['prioritas'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';


$filters = ["ditugaskan = ?"];
$params = [ &$username ];
$types = 's';

if (!empty($status)) {
    $filters[] = "status = ?";
    $params[] = &$status;
    $types .= "s";
}if (!empty($start_date) && !empty($end_date)) {
 
  if (strtotime($start_date) > strtotime($end_date)) {
      $temp = $start_date;
      $start_date = $end_date;
      $end_date = $temp;
  }

  $filters[] = "deadline BETWEEN ? AND ?";
  $params[] = &$start_date;
  $params[] = &$end_date;
  $types .= "ss";
} elseif (!empty($start_date)) {
  $filters[] = "deadline >= ?";
  $params[] = &$start_date;
  $types .= "s";
} elseif (!empty($end_date)) {
  $filters[] = "deadline <= ?";
  $params[] = &$end_date;
  $types .= "s";
}

if (!empty($prioritas)) {
    $filters[] = "prioritas = ?";
    $params[] = &$prioritas;
    $types .= "s";
}
if (!empty($search)) {
    $filters[] = "(nama_tugas LIKE ? OR status LIKE ? OR prioritas LIKE ? OR deadline LIKE ?)";
    $searchWildcard = "%" . $search . "%";
    $params[] = &$searchWildcard;
    $params[] = &$searchWildcard;
    $params[] = &$searchWildcard;
    $params[] = &$searchWildcard;
    $types .= "ssss";
}


$whereClause = count($filters) > 0 ? " WHERE " . implode(" AND ", $filters) : "";

$totalQuery = "SELECT COUNT(*) AS total FROM tasks" . $whereClause;
$stmt = $conn->prepare($totalQuery);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalRow = $result->fetch_assoc();
$totalRows = $totalRow['total'];

$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;

$query = "SELECT * FROM tasks" . $whereClause . " LIMIT ?, ?";
$params[] = &$offset;
$params[] = &$perPage;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
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
  <title>User | List Tugas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="user-tasks.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  
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
        <div class="item"><a href="user-tasks.php" class="active"><i class="fas fa-solid fa-list-check active"></i>List Tugas</a></div>
        <div class="item"><a href="user-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="user-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-user.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
        
    </div>
</aside>

<header> 
<h1>Data Semua Tugas</h1>

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
  <img src="<?= htmlspecialchars($photo ?: $default_photo); ?>?t=<?= time(); ?>" alt="Foto Profil <?= htmlspecialchars($username); ?>" class="profile-img" onclick="toggleDetails()" onerror="this.src='<?= $default_photo; ?>'">

    
    <div class="profile-details">
        <div class="profile-info">
           <img src="<?= htmlspecialchars($photo ?: $default_photo); ?>?t=<?= time(); ?>" alt="Foto Profil <?= htmlspecialchars($username); ?>" class="profile-img" onclick="toggleDetails()" onerror="this.src='<?= $default_photo; ?>'">

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
  <div class="filter-search-bar">
  <form action="user-tasks.php" method="GET" class="search-form">
    <input type="hidden" name="prioritas" value="<?= htmlspecialchars($_GET['prioritas'] ?? ''); ?>">
    <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status'] ?? ''); ?>">
    <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? ''); ?>">
    <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? ''); ?>">

    <div class="search-container">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" class="search-input">
        <button type="submit" class="search-btn">
            <i class="fa-solid fa-search"></i>
        </button>
    </div>
 </form>

    <div class="button-container">
        <a href="user-report.php" class="btn btn-report">
            <i class="fas fa-file-alt"></i> Report
        </a>
        <button id="openFilterModal" class="btn btn-filter"><i class="fa-solid fa-filter"></i> Filter</button>
    </div>
</div>



<div id="filterModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Filter Tugas</h2>
    <form action="user-tasks.php" method="GET" class="filter-form"  id="filterForm">
    <!-- Status Filter -->
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

    <!-- Deadline Filter -->
    <div class="deadline-filter">
        <label for="deadline">Deadline:</label>
        <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? ''; ?>" />
        <span>sampai</span>
        <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? ''; ?>" />
      </div>

    <!-- Pagination Filter -->
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

    <!-- Modal Footer -->
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
      <th>Tanggal Mulai</th>
      <th>Deadline</th>
      <th>Status</th>
      <th>Prioritas</th> 
      <th>Detail</th>
      <th>File Pengumpulan</th>
    </tr>
  </thead>
  <tbody>
  <?php if (count($rows) == 0) : ?>
  <tr>
    <td colspan="8" style="text-align: center; font-weight: bold;">
      <p>Belum ada tugas untuk Anda saat ini</p>
    </td>
  </tr>
<?php else : ?>
  <?php $i = $offset + 1; ?>
  <?php foreach ($rows as $row) : ?>
    <?php
    // Deadline tugas di-set ke jam 23:59:59
    $deadlineDate = new DateTime($row['deadline'] . ' 23:59:59');
    $currentDate = new DateTime();

    // Deadline dianggap lewat hanya jika currentDate sudah lebih dari jam 23:59:59 deadline
    $isExpired = $currentDate > $deadlineDate;
    ?>
    <tr class="task" data-deadline="<?= $row['deadline']; ?>">
      <td class="number-center"><?= $i; ?></td>
      <td><?= htmlspecialchars($row["nama_tugas"]); ?></td>
      <td><?= date('d-m-Y', strtotime($row["tanggal_mulai"])); ?></td>
      <td class="deadline"><?= date('d-m-Y', strtotime($row["deadline"])); ?></td>
      <td class="status"><?= htmlspecialchars($row["status"]); ?></td>
      <td class="prioritas"><?= htmlspecialchars($row["prioritas"]); ?></td>
      <td>
        <button class="btn view" onclick="openDetailModal(<?= $row['id']; ?>)">
            <i class="fas fa-eye"></i>  Lihat
        </button>
      </td>
      <td>
        <?php if ($row["status"] == "Menunggu Pengumpulan" && empty($row["file_pengumpulan"])) { ?>
          <button class="btn upload" onclick="openUploadModal(<?= $row['id']; ?>)" <?= $isExpired ? 'disabled' : ''; ?>>
            <i class="fas fa-upload"></i> Upload
          </button>
        <?php } elseif ($row["status"] == "Menunggu Approval" && !empty($row["file_pengumpulan"])) { ?>
          <button class="btn edit" onclick="openUploadModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row["file_pengumpulan"]); ?>')" <?= $isExpired ? 'disabled' : ''; ?>>
            <i class="fas fa-edit"></i> Edit
          </button>
          <button class="btn download" onclick="downloadFile('<?= $row['file_pengumpulan']; ?>')">
            <i class="fas fa-download"></i> Download
          </button>
        <?php } elseif ($row["status"] == "Butuh Revisi" && !empty($row["file_pengumpulan"])) { ?>
          <button class="btn edit" onclick="openUploadModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row["file_pengumpulan"]); ?>')" <?= $isExpired ? 'disabled' : ''; ?>>
            <i class="fas fa-edit"></i> Edit
          </button>
          <button class="btn download" onclick="downloadFile('<?= $row['file_pengumpulan']; ?>')">
            <i class="fas fa-download"></i> Download
          </button>
        <?php } elseif ($row["status"] == "Selesai" && !empty($row["file_pengumpulan"])) { ?>
          <button class="btn download" onclick="downloadFile('<?= $row['file_pengumpulan']; ?>')">
            <i class="fas fa-download"></i> Download
          </button>
        <?php } ?>
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

        <!-- Tombol navigasi tetap ada, tapi dinonaktifkan -->
        <a href="#" class="page-link disabled"><i class="fa-duotone fa-solid fa-backward-fast"></i></a>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-backward-step"></i></a>
           <span class="current-page">Halaman 0 dari 0</span>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-forward-step"></i></a>
        <a href="#" class="page-link disabled"><i class="fa-solid fa-forward-fast"></i></a>
    <?php endif; ?>
</div>


  </main>

  
<div id="taskDetailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="taskDetailContent"></div>  
    </div>
</div>


<div id="uploadFileModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Upload File Tugas</h3>
        <form id="uploadForm" class="upload-form" action="user-upload-file.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="task_id" id="task_id">
            <input type="file" name="file_pengumpulan" <?php echo isset($row['file_pengumpulan']) ? '' : 'required'; ?>>
            <button type="submit" name="upload_file" class="btn save">Upload</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="user-tasks.js"></script>
</body>
</html>