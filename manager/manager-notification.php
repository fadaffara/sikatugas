//ABAIKAN DULU//


<?php
require_once("../config.php");
session_start(); 

// Cek apakah admin sudah login
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-admin.php"); 
    exit;
}

// Ambil email admin dari sesi
$admin_email = $_SESSION['email_admin'];

// Ambil ID admin berdasarkan email
$query = "SELECT id FROM admin WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $admin_id = $row['id'];
} else {
    die(json_encode(['error' => 'Admin ID tidak ditemukan.']));
}

// Dapatkan tanggal saat ini dengan waktu 00:00:00
$current_date = new DateTime();
$current_date->setTime(0, 0, 0);

// Query untuk mendapatkan semua tugas
$sql = "SELECT id, nama_tugas, deadline FROM tasks";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die(json_encode(['error' => 'Query gagal: ' . mysqli_error($conn)]));
}

while ($task = mysqli_fetch_assoc($result)) {
    $deadline = new DateTime($task['deadline']);
    $interval = $current_date->diff($deadline);

    $message = '';
    $tipe = 'Deadline';

    if ($interval->invert == 1) {
        $message = "Deadline tugas **{$task['nama_tugas']}** telah lewat pada tanggal **{$deadline->format('d F Y')}**.";
    } elseif ($interval->days == 0) {
        $message = "Deadline tugas **{$task['nama_tugas']}** adalah hari ini, tanggal **{$deadline->format('d F Y')}**.";
    } elseif ($interval->days == 1) {
        $message = "Deadline tugas **{$task['nama_tugas']}** adalah besok, tanggal **{$deadline->format('d F Y')}**.";
    } elseif ($interval->days <= 3) {
        $message = "Deadline tugas **{$task['nama_tugas']}** tinggal {$interval->days} hari lagi, yaitu pada tanggal **{$deadline->format('d F Y')}**.";
    }

    if ($message != '') {
        $deleteQuery = "DELETE FROM notifikasi WHERE task_id = ? AND tipe = ? AND admin_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('iss', $task['id'], $tipe, $admin_id);
        $stmt->execute();

        $insertQuery = "INSERT INTO notifikasi (task_id, tipe, pesan, tanggal_dibuat, admin_id) 
                        VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('isss', $task['id'], $tipe, $message, $admin_id);
        $stmt->execute();
    }
}
// Ambil semua notifikasi terbaru untuk dikirimkan ke frontend
$notifQuery = "SELECT pesan, tipe FROM notifikasi WHERE admin_id = ? ORDER BY tanggal_dibuat DESC";
$stmt = $conn->prepare($notifQuery);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$notifikasi = [];
while ($notif = $result->fetch_assoc()) {
    $notifikasi[] = [
        'pesan' => $notif['pesan'],
        'tipe' => $notif['tipe']
    ];
}

header('Content-Type: application/json');
echo json_encode($notifikasi);

?>
