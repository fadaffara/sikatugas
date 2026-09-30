<?php 
    require_once ("../config.php");
    session_start(); 
    if (!isset($_SESSION['login_admin']) || !isset($_SESSION['name_manager'])) {
        header("Location: login-manager.php"); 
        exit;
    }

    $queryTotal = "SELECT COUNT(*) AS total FROM tasks";
    $resultTotal = $conn->query($queryTotal);
    $totalTasks = $resultTotal->fetch_assoc()['total'];

    $queryInProgress = "SELECT COUNT(*) AS waiting FROM tasks WHERE status = 'menunggu pengumpulan'";
    $queryApproval = "SELECT COUNT(*) AS approval FROM tasks WHERE status = 'menunggu approval'";
    $queryRevision = "SELECT COUNT(*) AS revision FROM tasks WHERE status = 'butuh revisi'";
    $queryDone = "SELECT COUNT(*) AS done FROM tasks WHERE LOWER(status) = 'selesai'";
    $queryLate = "SELECT COUNT(*) AS late FROM tasks WHERE deadline < NOW() AND LOWER(status) != 'selesai'";

    $resultInProgress = $conn->query($queryInProgress);
    $resultApproval = $conn->query($queryApproval);
    $resultRevision = $conn->query($queryRevision);
    $resultDone = $conn->query($queryDone);
    $resultLate = $conn->query($queryLate);

    $inProgressTasks = $resultInProgress->fetch_assoc()['waiting'];
    $approvalTasks = $resultApproval->fetch_assoc()['approval'];
    $revisionTasks = $resultRevision->fetch_assoc()['revision'];
    $doneTasks = $resultDone->fetch_assoc()['done'];
    $lateTasks = $resultLate->fetch_assoc()['late'];

    $pendingTasks = $totalTasks - $doneTasks;

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

    $priorityStats = [];
    $priorities = ['Tinggi', 'Sedang', 'Rendah'];
    foreach ($priorities as $priority) {
        $query = "SELECT COUNT(*) AS total FROM tasks WHERE prioritas = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $priority);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $priorityStats[$priority] = $row['total'] ?? 0;
    }

    $statusStats = [];
    $statuses = ['menunggu pengumpulan', 'menunggu approval', 'butuh revisi', 'selesai'];
    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) AS total FROM tasks WHERE status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $statusStats[$status] = $row['total'] ?? 0;
    }

    function formatDurasi($jamFloat) {
        $totalMenit = ceil($jamFloat * 60); 

    
        $hari = floor($totalMenit / 1440); 
        $sisaMenit = $totalMenit % 1440;
    
        $jam = floor($sisaMenit / 60);
        $menit = $sisaMenit % 60;
    
        $hasil = "";
        if ($hari > 0) $hasil .= "$hari hari ";
        if ($jam > 0) $hasil .= "$jam jam ";
        if ($menit > 0 || $hasil == "") $hasil .= "$menit menit";
    
        return trim($hasil);
    }
    

    // Ambil semua user
    $userQuery = "SELECT DISTINCT ditugaskan AS username FROM tasks";
    $userResult = $conn->query($userQuery);


    $userPerformance = [];

    while ($user = $userResult->fetch_assoc()) {
        $username = $user['username'];
        // Ambil nama user dari tabel users berdasarkan username
        $nameQuery = "SELECT name FROM users WHERE username = ?";
        $nameStmt = $conn->prepare($nameQuery);
        $nameStmt->bind_param("s", $username);
        $nameStmt->execute();
        $nameResult = $nameStmt->get_result();
        $nameRow = $nameResult->fetch_assoc();
        $realName = $nameRow ? $nameRow['name'] : $username; // fallback ke username kalau gak ketemu


        $taskQuery = "SELECT * FROM tasks WHERE ditugaskan = ?";
        $stmt = $conn->prepare($taskQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $tasks = $stmt->get_result();

        $total = $done = $late = $totalDurasi = $totalRevisi = 0;

        while ($task = $tasks->fetch_assoc()) {
            
            $total++;
            $totalDurasi += $task['durasi_pengerjaan'];
            $totalRevisi += $task['total_revisi'];
        
            if (strtolower($task['status']) === 'selesai') {
                $done++;
                // Gak perlu hitung telat di sini karena udah selesai
            } elseif (strtotime($task['deadline']) < time()) {
                // Belum selesai dan sudah lewat deadline = telat
                $late++;
            }
        }
        

        if ($total === 0) continue; // skip user tanpa tugas

        $avgDurasi = $totalDurasi / $total; // masih dalam jam
        $avgDurasiMenit = $avgDurasi * 60; // langsung pakai float menit tanpa dibulatkan dulu        
        $avgRevisi = $totalRevisi / $total;
        $donePercent = ($done / $total) * 100;
        $latePercent = ($late / $total) * 100;

        $performance = ($avgDurasi <= 1 && $avgRevisi <= 2 && $donePercent >= 80 && $latePercent <=20) ? 'Baik' : 'Kurang';


        $userPerformance[] = [
            'username' => $username,
            'name' => $realName, // ini tambahan biar bisa ditampilkan di tabel
            'avgDurasi' => formatDurasi($avgDurasi),
            'avgDurasiMenit' => round($avgDurasiMenit, 2),
            'avgRevisi' => round($avgRevisi, 2),
            'donePercent' => round($donePercent, 1),
            'latePercent' => round($latePercent, 1),
            'performance' => $performance
        ];
        
        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manager | Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="manager-dashboard.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="item"><a href="manager-dashboard.php" class="active"><i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="manager-tasks.php"><i class="fas fa-solid fa-list-check"></i>Data Tugas User </a></div>
        <div class="item"><a href="manager-insert.php"><i class="fas fa-solid fa-plus"></i>Tambah Tugas </a></div>
        <div class="item"><a href="manager-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="manager-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-manager.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
    </div>
</aside>

<header> 
<h1>Dashboard</h1>

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
    <div class="dashboard-container">
        <div class="stats">
            <div class="stat total">
                <h3>Total Semua Tugas</h3>
                <p><?= $totalTasks ?></p>
            </div>
            <div class="stat done">
                <h3>Total Tugas Selesai</h3>
                <p><?= $doneTasks ?></p>
            </div>
            <div class="stat pending">
                <h3>Total Tugas Belum Selesai</h3>
                <p><?= $pendingTasks ?></p>
            </div>
            <div class="stat late">
                <h3>Total Tugas Lewat Deadline</h3>
                <p><?= $lateTasks ?></p>
            </div>
        </div>
    </div>


<div class="progress-section">
    <h2>Overview Persentase Status Tugas</h2>
    <div style="width: 25%; margin: auto;">
    <canvas id="statusChart"></canvas>
    </div>

    <h2>Overview Persentase Prioritas Tugas</h2>
    <div style="width: 25%; margin: auto;">
    <canvas id="priorityChart"></canvas>
    </div>

    <h2>Overview Performa User</h2>
    <div>
    <table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>Nama User</th>
            <th>Rata-Rata Durasi Pengerjaan Tugas</th>
            <th>Rata-Rata Total Revisi</th>
            <th>% Tugas Selesai</th>
            <th>% Tugas Telat</th>
            <th>Performa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($userPerformance as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= $u['avgDurasi'] ?></td>
            <td><?= $u['avgRevisi'] ?></td>
            <td><?= $u['donePercent'] ?>%</td>
            <td><?= $u['latePercent'] ?>%</td>
            <td><?= $u['performance'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    
</table>
<strong>Keterangan:</strong>
    <ul>
     
        <li><strong>Penilaian kinerja</strong> akan muncul sebagai "<em>Baik</em>" jika:
            <ul>
                <li>Rata-rata durasi pengerjaan tugas ≤ 1 hari</li>
                <li>Rata-rata total revisi ≤ 2 kali</li>
                <li>≥ 80% untuk tugas selesai</li>
                <li>≤ 20% untuk tugas telat</li>
            </ul>
            Jika salah satu dari keempat kriteria tersebut tidak terpenuhi, maka dinilai "<em>Kurang</em>".
        </li>
    </ul>
    </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    const statusLabels = <?= json_encode(array_map('ucwords', array_keys($statusStats))) ?>;
    const statusValues = <?= json_encode(array_values($statusStats)) ?>;
    const priorityLabels = <?= json_encode(array_keys($priorityStats)) ?>;
    const priorityValues = <?= json_encode(array_values($priorityStats)) ?>;

   
    const filteredStatusLabels = statusLabels.filter((label, index) => statusValues[index] > 0);
    const filteredStatusValues = statusValues.filter(value => value > 0);

    const filteredPriorityLabels = priorityLabels.filter((label, index) => priorityValues[index] > 0);
    const filteredPriorityValues = priorityValues.filter(value => value > 0);

    const totalStatus = filteredStatusValues.reduce((a, b) => a + b, 0);
    const statusPercentages = filteredStatusValues.map(val => ((val / totalStatus) * 100).toFixed(2));
    const totalCalculatedPercentage = statusPercentages.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
    const difference = 100 - totalCalculatedPercentage;
    if (statusPercentages.length > 0) {
       
        statusPercentages[statusPercentages.length - 1] = (parseFloat(statusPercentages[statusPercentages.length - 1]) + difference).toFixed(2);
    }

    const totalPriority = filteredPriorityValues.reduce((a, b) => a + b, 0);
    const priorityPercentages = filteredPriorityValues.map(val => ((val / totalPriority) * 100).toFixed(2));
    const totalCalculatedPriorityPercentage = priorityPercentages.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
    const priorityDifference = 100 - totalCalculatedPriorityPercentage;

    if (priorityPercentages.length > 0) {
       
        priorityPercentages[priorityPercentages.length - 1] = (parseFloat(priorityPercentages[priorityPercentages.length - 1]) + priorityDifference).toFixed(2);
    }

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');

    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: filteredStatusLabels,
            datasets: [{
                data: statusPercentages,
                backgroundColor: ['#89CFF0', '#FF7043', '#C8E6C9', '#2C6B3F'],  
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        boxHeight: 15,
                        padding: 10,
                        font: { size: 14 },
                        color: '#333',
                        usePointStyle: true
                    }
                },
                datalabels: {
                    formatter: (value) => value + '%',
                    color: '#fff',
                    font: { weight: 'bold', size: 14 }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const index = context.dataIndex;
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const jumlah = filteredStatusValues[index]; 
                            return `${label}: ${value}% (${jumlah} tugas)`;
                        }
                    }
                }

            },
            layout: {
                padding: { bottom: 20 }
            }
        },
        plugins: [ChartDataLabels]
    });

    new Chart(priorityCtx, {
        type: 'pie',
        data: {
            labels: filteredPriorityLabels,
            datasets: [{
                data: priorityPercentages,
                backgroundColor: ['#e63946', '#ffca3a', '#FFCCBC'],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        boxHeight: 15,
                        padding: 10,
                        font: { size: 14 },
                        color: '#333',
                        usePointStyle: true
                    }
                },
                datalabels: {
                    formatter: (value) => value + '%',
                    color: '#fff',
                    font: { weight: 'bold', size: 14 }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const index = context.dataIndex;
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const jumlah = filteredPriorityValues[index]; 
                            return `${label}: ${value}% (${jumlah} tugas)`;
                        }
                    }
                }

            },
            layout: {
                padding: { bottom: 20 }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>


</body>