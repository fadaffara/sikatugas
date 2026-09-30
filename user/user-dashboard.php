<?php
include("../config.php");
session_start();

if (!isset($_SESSION['login_user']) || !isset($_SESSION['email_user']) || !isset($_SESSION['username_user']) || !isset($_SESSION['name_user']) || !isset($_SESSION['photo_user'])) {
    header("Location: login-user.php");
    exit;
}

$email    = $_SESSION['email_user'] ?? '';
$username = $_SESSION['username_user'] ?? '';
$name     = $_SESSION['name_user'] ?? '';
$photo    = $_SESSION['photo_user'] ?? '';
$default_photo = "../images/photo-of-user.png";

$user_id = $_SESSION['user_id']; 


$queryTotalUserTasks = "SELECT COUNT(*) AS total FROM tasks WHERE ditugaskan = ?";
$queryNotStartedUserTasks = "SELECT COUNT(*) AS not_started FROM tasks WHERE ditugaskan = ? AND status = 'belum dimulai'";
$queryInProgressUserTasks = "SELECT COUNT(*) AS in_progress FROM tasks WHERE ditugaskan = ? AND status = 'sedang dikerjakan'";
$queryDoneUserTasks = "SELECT COUNT(*) AS done FROM tasks WHERE ditugaskan = ? AND status = 'selesai'";
$queryLateUserTasks = "SELECT COUNT(*) AS late FROM tasks WHERE ditugaskan = ? AND deadline < NOW() AND status != 'selesai'";
$queryWaitingApprovalUserTasks = "SELECT COUNT(*) AS waiting_approval FROM tasks WHERE ditugaskan = ? AND status = 'menunggu approval'";
$queryRevisionNeededUserTasks = "SELECT COUNT(*) AS revision_needed FROM tasks WHERE ditugaskan = ? AND status = 'butuh revisi'";


$stmtTotal = $conn->prepare($queryTotalUserTasks);
$stmtTotal->bind_param("s", $username);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalUserTasks = $resultTotal->fetch_assoc()['total'];

$stmtNotStarted = $conn->prepare($queryNotStartedUserTasks);
$stmtNotStarted->bind_param("s", $username);
$stmtNotStarted->execute();
$resultNotStarted = $stmtNotStarted->get_result();
$notStartedUserTasks = $resultNotStarted->fetch_assoc()['not_started'];

$stmtInProgress = $conn->prepare($queryInProgressUserTasks);
$stmtInProgress->bind_param("s", $username);
$stmtInProgress->execute();
$resultInProgress = $stmtInProgress->get_result();
$inProgressUserTasks = $resultInProgress->fetch_assoc()['in_progress'];

$stmtDone = $conn->prepare($queryDoneUserTasks);
$stmtDone->bind_param("s", $username);
$stmtDone->execute();
$resultDone = $stmtDone->get_result();
$doneUserTasks = $resultDone->fetch_assoc()['done'];

$stmtLate = $conn->prepare($queryLateUserTasks);
$stmtLate->bind_param("s", $username);
$stmtLate->execute();
$resultLate = $stmtLate->get_result();
$lateUserTasks = $resultLate->fetch_assoc()['late'];

$stmtWaitingApproval = $conn->prepare($queryWaitingApprovalUserTasks);
$stmtWaitingApproval->bind_param("s", $username);
$stmtWaitingApproval->execute();
$resultWaitingApproval = $stmtWaitingApproval->get_result();
$waitingApprovalUserTasks = $resultWaitingApproval->fetch_assoc()['waiting_approval'];

$stmtRevisionNeeded = $conn->prepare($queryRevisionNeededUserTasks);
$stmtRevisionNeeded->bind_param("s", $username);
$stmtRevisionNeeded->execute();
$resultRevisionNeeded = $stmtRevisionNeeded->get_result();
$revisionNeededUserTasks = $resultRevisionNeeded->fetch_assoc()['revision_needed'];

$pendingUserTasks = $totalUserTasks - $doneUserTasks;


$priorityStatsUser = [];
$prioritiesUser = ['Tinggi', 'Sedang', 'Rendah'];

foreach ($prioritiesUser as $priority) {
    $query = "SELECT COUNT(*) AS total FROM tasks WHERE ditugaskan = ? AND prioritas = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $priority);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $priorityStatsUser[$priority] = $row['total'] ?? 0;
}

$queryWaitingSubmitUserTasks = "SELECT COUNT(*) AS waiting_submit FROM tasks WHERE ditugaskan = ? AND status = 'menunggu pengumpulan'";

$stmtWaitingSubmit = $conn->prepare($queryWaitingSubmitUserTasks);
$stmtWaitingSubmit->bind_param("s", $username);
$stmtWaitingSubmit->execute();
$resultWaitingSubmit = $stmtWaitingSubmit->get_result();
$waitingSubmitUserTasks = $resultWaitingSubmit->fetch_assoc()['waiting_submit'];
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User | Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="user-dashboard.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="item"><a href="#" class="active"><i class="fas fa-desktop"></i>Dashboard</a></div>
        <div class="item"><a href="user-tasks.php"><i class="fas fa-solid fa-list-check"></i>List Tugas</a></div>
        <div class="item"><a href="user-report.php"><i class="fas fa-file-alt"></i>Report</a></div>
        <div class="item"><a href="user-profile.php"><i class="fas fa-user-edit"></i>Edit Profil</a></div>
        <div class="item"><a href="logout-user.php"><i class="fas fa-solid fa-right-from-bracket"></i>Logout</a></div>
        
    </div>
    
</aside>



<header> 
<h1>Dashboard </h1>

  <div class="notification">
    <!-- <i class="fas fa-bell"></i> -->
  </div>

  <div class="profile">
    <img src="<?= htmlspecialchars($photo) ? htmlspecialchars($photo) : $default_photo; ?>" alt="Profile" class="profile-img" 
        onclick="toggleDetails()" onerror="this.src='<?= $default_photo; ?>'">
        
    <div class="profile-details">
        <div class="profile-info">
            <img src="<?= htmlspecialchars($photo) ? htmlspecialchars($photo) : $default_photo; ?>" alt="Profile" class="profile-sub-img" 
                 onerror="this.src='<?= $default_photo; ?>'">
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
    <div class="dashboard-container">
        <div class="stats">
            <div class="stat total">
                <h3>Total Tugas</h3>
                <p><?= $totalUserTasks ?></p>
            </div>
            <div class="stat done">
                <h3>Tugas Selesai</h3>
                <p><?= $doneUserTasks ?></p>
            </div>
            <div class="stat pending">
                <h3>Tugas Belum Selesai</h3>
                <p><?= $pendingUserTasks ?></p>
            </div>
            <div class="stat late">
                <h3>Tugas Lewat Deadline</h3>
                <p><?= $lateUserTasks ?></p>
            </div>
        </div>
    </div>

    <div class="progress-section">
       
    <h2>Overview Status Tugas</h2>
<div style="width: 25%; margin: auto;">
    <canvas id="statusChartUser"></canvas>
</div>


    <h2>Overview Prioritas Tugas</h2>
    <div style="width: 25%; margin: auto;">
    <canvas id="priorityChartUser"></canvas>
    </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const ctxUser = document.getElementById('priorityChartUser').getContext('2d');
    const totalTasksUser = <?= array_sum($priorityStatsUser) ?>;

  
    const labelsPriority = [];
    const dataPriority = [];
    const backgroundColorPriority = ['#FF0000', '#FFC300', '#E1BEE7'];
    const borderColorPriority = ['#FF0000', '#FFC300', '#E1BEE7'];

    if (<?= $priorityStatsUser['Tinggi'] ?> > 0) {
        labelsPriority.push('Tinggi');
        dataPriority.push(<?= $priorityStatsUser['Tinggi'] ?>);
    }
    if (<?= $priorityStatsUser['Sedang'] ?> > 0) {
        labelsPriority.push('Sedang');
        dataPriority.push(<?= $priorityStatsUser['Sedang'] ?>);
    }
    if (<?= $priorityStatsUser['Rendah'] ?> > 0) {
        labelsPriority.push('Rendah');
        dataPriority.push(<?= $priorityStatsUser['Rendah'] ?>);
    }

    const totalCalculatedPriorityPercentage = dataPriority.reduce((a, b) => (a + (b / totalTasksUser) * 100), 0);
    const priorityDifference = 100 - totalCalculatedPriorityPercentage;


    if (dataPriority.length > 0) {
        dataPriority[dataPriority.length - 1] = (dataPriority[dataPriority.length - 1] + priorityDifference).toFixed(2);
    }

    const dataUser = {
        labels: labelsPriority,
        datasets: [{
            label: 'Jumlah Tugas Berdasarkan Prioritas',
            data: dataPriority,
            backgroundColor: backgroundColorPriority.slice(0, dataPriority.length),
            borderColor: borderColorPriority.slice(0, dataPriority.length),
            borderWidth: 1
        }]
    };

    const configUser = {
        type: 'pie',
        data: dataUser,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const percentage = ((value / totalTasksUser) * 100).toFixed(2);
                            return `${label}: ${value} tugas (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    formatter: function(value) {
                        const percentage = ((value / totalTasksUser) * 100).toFixed(2);
                        return `${percentage}%`;
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    };

    new Chart(ctxUser, configUser);
</script>

<script>
    const ctxStatus = document.getElementById('statusChartUser').getContext('2d');
    const totalStatus = <?= $waitingSubmitUserTasks + $waitingApprovalUserTasks + $revisionNeededUserTasks + $doneUserTasks ?>;

   
    const labelsStatus = [];
    const dataStatus = [];
    const backgroundColor = ['#89CFF0', '#FF7043', '#C8E6C9', '#2C6B3F'];
    const borderColor = ['#89CFF0', '#FF7043', '#C8E6C9', '#2C6B3F'];


    if (<?= $waitingSubmitUserTasks ?> > 0) {
        labelsStatus.push('Menunggu Pengumpulan');
        dataStatus.push(<?= $waitingSubmitUserTasks ?>);
    }
    if (<?= $waitingApprovalUserTasks ?> > 0) {
        labelsStatus.push('Menunggu Approval');
        dataStatus.push(<?= $waitingApprovalUserTasks ?>);
    }
    if (<?= $revisionNeededUserTasks ?> > 0) {
        labelsStatus.push('Butuh Revisi');
        dataStatus.push(<?= $revisionNeededUserTasks ?>);
    }
    if (<?= $doneUserTasks ?> > 0) {
        labelsStatus.push('Selesai');
        dataStatus.push(<?= $doneUserTasks ?>);
    }

    const totalCalculatedStatusPercentage = dataStatus.reduce((a, b) => (a + (b / totalStatus) * 100), 0);
    const statusDifference = 100 - totalCalculatedStatusPercentage;

 
    if (dataStatus.length > 0) {
        dataStatus[dataStatus.length - 1] = (dataStatus[dataStatus.length - 1] + statusDifference).toFixed(2);
    }

    const statusData = {
        labels: labelsStatus,
        datasets: [{
            label: 'Status Tugas',
            data: dataStatus,
            backgroundColor: backgroundColor.slice(0, dataStatus.length),
            borderColor: borderColor.slice(0, dataStatus.length),
            borderWidth: 1
        }]
    };

    const configStatus = {
        type: 'pie',
        data: statusData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const percentage = ((value / totalStatus) * 100).toFixed(2);
                            return `${label}: ${value} tugas (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    formatter: function(value) {
                        const percentage = ((value / totalStatus) * 100).toFixed(2);
                        return `${percentage}%`;
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    };

    new Chart(ctxStatus, configStatus);
</script>



    

<script src="user-dashboard.js"></script>

</body>
</html>