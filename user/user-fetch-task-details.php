<?php
require ("../config.php");

if (isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    if ($task) {
        echo "<h3>" . htmlspecialchars($task['nama_tugas']) . "</h3>";
        echo "<p><strong>Tanggal Mulai:</strong> " . date('d-m-Y', strtotime($task['tanggal_mulai'])) . "</p>";
        echo "<p><strong>Deadline:</strong> " . date('d-m-Y', strtotime($task['deadline'])) . "</p>";
        echo "<p><strong>Prioritas:</strong> " . htmlspecialchars($task['prioritas']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($task['status']) . "</p>";

        // Tampilkan file admin jika ada
        if (!empty($task['file_admin'])) {
            echo "<p><strong>File Admin:</strong> <a href='../uploads/" . htmlspecialchars($task['file_admin']) . "' download>Download</a></p>";
        } else {
            echo "<p><strong>File Admin:</strong> Kosong</p>";
        }

        // Tampilkan detail tugas jika ada
        if (!empty($task['detail_tugas'])) {
            echo "<p><strong>Detail Tugas:</strong> " . nl2br(htmlspecialchars($task['detail_tugas'])) . "</p>";
        } else {
            echo "<p><strong>Detail Tugas:</strong> Tidak ada detail yang diberikan.</p>";
        }

     

        // Tampilkan waktu selesai jika tugas sudah selesai
        if ($task['status'] === "Selesai" && !empty($task['waktu_selesai'])) {
            echo "<p><strong>Waktu Selesai:</strong> " . date('d-m-Y H:i', strtotime($task['waktu_selesai'])) . "</p>";
            
            // Menghitung durasi pengerjaan jika waktu selesai ada
            $start = strtotime($task['tanggal_mulai']);
            $end = strtotime($task['waktu_selesai']);
            $duration = $end - $start;

            // Menghitung hari, jam, menit
            $days = floor($duration / (60 * 60 * 24));
            $hours = floor(($duration % (60 * 60 * 24)) / (60 * 60));
            $minutes = floor(($duration % (60 * 60)) / 60);

            $durationString = "";
            if ($days > 0) {
                $durationString .= $days . " hari ";
            }
            if ($hours > 0) {
                $durationString .= $hours . " jam ";
            }
            if ($minutes > 0) {
                $durationString .= $minutes . " menit";
            }

            echo "<p><strong>Durasi Pengerjaan:</strong> " . $durationString . "</p>";
        }

        // Menampilkan total revisi
        echo "<p><strong>Total Revisi:</strong> " . (isset($task['total_revisi']) ? $task['total_revisi'] : 0) . "</p>";

    } else {
        echo "<p>Detail tugas tidak ditemukan.</p>";
    }
}
?>
