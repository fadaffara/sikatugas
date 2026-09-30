<?php
require_once("../config.php");

// Ambil semua tugas
$sql = "SELECT id, deadline, file_admin FROM tasks";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $deadline = new DateTime($row['deadline']);
        $file_admin = $row['file_admin'];
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $deadline_clone = clone $deadline;
        $deadline_clone->setTime(0, 0, 0);
        $interval = $today->diff($deadline_clone)->days;
        $is_future = $deadline_clone >= $today;

        // Tentukan prioritas
        if ($is_future && $interval <= 2) {
            $prioritas = 'Tinggi';
        } elseif ($is_future && $interval >= 3) {
            $prioritas = !empty($file_admin) ? 'Sedang' : 'Rendah';
        } else {
            $prioritas = 'Tinggi'; // Jika deadline sudah lewat
        }

        // Update ke database jika prioritas berubah
        $update_sql = "UPDATE tasks SET prioritas = '$prioritas' WHERE id = $id";
        mysqli_query($conn, $update_sql);
    }

} else {
    echo "Gagal mengambil data tugas: " . mysqli_error($conn);
}
?>
