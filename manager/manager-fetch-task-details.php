<?php 
require_once ("../config.php");

if (isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    if ($task) {
        $divisiQuery = "SELECT d.nama_divisi FROM users u
        LEFT JOIN divisi d ON u.divisi_id = d.kode_divisi
        WHERE u.username = ?";
        $divisiStmt = $conn->prepare($divisiQuery);
        $divisiStmt->bind_param("s", $task['ditugaskan']);
        $divisiStmt->execute();
        $divisiResult = $divisiStmt->get_result();
        $divisiRow = $divisiResult->fetch_assoc();
        $namaDivisi = $divisiRow ? $divisiRow['nama_divisi'] : 'Tidak diketahui';
        echo "<h3>" . htmlspecialchars($task['nama_tugas']) . "</h3>";
        echo "<p><strong>Ditugaskan:</strong> " . htmlspecialchars($task['ditugaskan']) . "</p>";
        echo "<p><strong>Divisi:</strong> " . htmlspecialchars($namaDivisi) . "</p>";        
        echo "<p><strong>Tanggal Mulai:</strong> " . date('d-m-Y', strtotime($task['tanggal_mulai'])) . "</p>";

        $today = strtotime(date('Y-m-d'));
        $deadline = strtotime($task['deadline']);
        $deadlineClass = '';

       
        if ($deadline < $today) {
            $deadlineClass = 'deadline-lewat';
        } elseif ($deadline == $today) {
            $deadlineClass = 'deadline-hari-ini';
        } else {
            $deadlineClass = 'deadline-aman';
        }
    

        echo "<p><strong>Deadline:</strong> <span class='$deadlineClass'>" . date('d-m-Y', $deadline) . "</span></p>";

        echo "<p><strong>Status:</strong> " . htmlspecialchars($task['status']) . "</p>";
        echo "<p><strong>Prioritas:</strong> " . htmlspecialchars($task['prioritas']) . "</p>";

        if ($task['status'] === "Selesai" && !empty($task['waktu_selesai'])) {
            echo "<p><strong>Waktu Selesai:</strong> " . date('d-m-Y H:i', strtotime($task['waktu_selesai'])) . "</p>";

            // Menampilkan durasi pengerjaan dalam format hari, jam, menit
            if (!empty($task['durasi_pengerjaan'])) {
                $durasiTotalMenit = $task['durasi_pengerjaan'] * 60; // durasi dalam menit
                $durasiHari = floor($durasiTotalMenit / 1440); // 1 hari = 1440 menit
                $durasiJam = floor(($durasiTotalMenit % 1440) / 60); // Jam sisa setelah dikurangi hari
                $durasiMenit = $durasiTotalMenit % 60; // Menit sisa setelah dikurangi jam

                $durasi = '';
                if ($durasiHari > 0) {
                    $durasi .= $durasiHari . ' hari ';
                }
                if ($durasiJam > 0) {
                    $durasi .= $durasiJam . ' jam ';
                }
                if ($durasiMenit > 0) {
                    $durasi .= $durasiMenit . ' menit';
                }

                echo "<p><strong>Durasi Pengerjaan:</strong> " . $durasi . "</p>";
            }
        }

        // Menampilkan total revisi
        echo "<p><strong>Total Revisi:</strong> " . (isset($task['total_revisi']) ? $task['total_revisi'] : 0) . "</p>";

        // Menampilkan file pengumpulan jika ada
        if (!empty($task['file_pengumpulan'])) {
            echo "<p><strong>File Pengumpulan:</strong> <a href='../user/upload/" . htmlspecialchars($task['file_pengumpulan']) . "' download>Download</a></p>";
        } else {
            echo "<p><strong>File Pengumpulan:</strong> Kosong</p>";
        }

        if (!empty($task['file_admin'])) {
            echo "<p><strong>File Admin:</strong> <a href='/upload/" . htmlspecialchars($task['file_admin']) . "' download>Download</a></p>";
        } else {
            echo "<p><strong>File Admin:</strong> Kosong</p>";
        }

        // Menampilkan detail tugas jika ada
        if (!empty($task['detail_tugas'])) {
            echo "<p><strong>Detail Tugas:</strong> " . nl2br(htmlspecialchars($task['detail_tugas'])) . "</p>";
        } else {
            echo "<p><strong>Detail Tugas:</strong> Tidak ada detail yang diberikan.</p>";
        }

        // Tombol berdasarkan status
        if ($task['status'] === "Selesai") {
            echo "<form action='manager-approve-reject.php' method='POST'>";
            echo "<input type='hidden' name='task_id' value='" . htmlspecialchars($task['id']) . "'>";
            echo "<input type='hidden' name='action' value='request_revision'>";
            echo "<button type='submit' class='btn-revision'><i class='fas fa-edit'></i> Ajukan Revisi</button>";
            echo "</form>";
        } elseif ($task['status'] === "Menunggu Approval") {
            echo "<form action='manager-approve-reject.php' method='POST' style='display: inline-block;'>";
            echo "<input type='hidden' name='task_id' value='" . htmlspecialchars($task['id']) . "'>";
            echo "<button type='submit' name='action' value='approve' class='btn-approve'><i class='fas fa-check'></i> Approve</button>";
            echo "</form>";

            echo "<form action='manager-approve-reject.php' method='POST' style='display: inline-block;'>";
            echo "<input type='hidden' name='task_id' value='" . htmlspecialchars($task['id']) . "'>";
            echo "<button type='submit' name='action' value='reject' class='btn-reject'><i class='fas fa-times'></i> Reject</button>";
            echo "</form>";
        }

    } else {
        echo "<p>Detail tugas tidak ditemukan.</p>";
    }
}
?>
