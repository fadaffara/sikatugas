<?php
require("../config.php");

if (isset($_POST['task_id']) && isset($_POST['action'])) {
    $taskId = $_POST['task_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Update status jadi selesai, waktu_selesai, dan hitung durasi pengerjaan dalam jam
        $updateQuery = "UPDATE tasks 
                        SET status = 'Selesai', 
                            waktu_selesai = NOW(),
                            durasi_pengerjaan = TIMESTAMPDIFF(SECOND, tanggal_mulai, NOW()) / 3600
                        WHERE id = ?";
    } 
    elseif ($action == 'reject' || $action == 'request_revision') {
        // Update status dan tambah total_revisi +1
        $updateQuery = "UPDATE tasks 
                        SET status = 'Butuh Revisi', 
                            total_revisi = IFNULL(total_revisi, 0) + 1 
                        WHERE id = ?";
    }

    if (isset($updateQuery)) {
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $taskId);

        if ($stmt->execute()) {
            header("Location: manager-tasks.php");
            exit;
        } else {
            echo "Gagal memperbarui status tugas.";
        }
    }
}
?>
