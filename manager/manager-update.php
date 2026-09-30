<?php
session_start();
require_once("../config.php");

if (isset($_POST['id'])) {
    // Ambil data dari form
    $id = intval($_POST['id']);
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $detail_tugas = mysqli_real_escape_string($conn, $_POST['detail_tugas']);
    $ditugaskan = mysqli_real_escape_string($conn, $_POST['ditugaskan']);
    $deadline = $_POST['deadline'];
    $prioritas = mysqli_real_escape_string($conn, $_POST['prioritas']);

    // Validasi tanggal
    $current_date = date('Y-m-d');
    if ($deadline < $current_date) {
        die("Error: Deadline tidak boleh di masa lalu.");
    }

    // Proses file jika ada
    $file_to_save = $_POST['file_lama']; // default

// Kalau user centang hapus_file
if (isset($_POST['hapus_file']) && $_POST['hapus_file'] == "1") {
    if (!empty($_POST['file_lama']) && file_exists("uploads/" . $_POST['file_lama'])) {
        unlink("uploads/" . $_POST['file_lama']);
    }
    $file_to_save = null;
}

// Kalau ada file baru di-upload, overwrite file lama
if (isset($_FILES['file_admin']) && $_FILES['file_admin']['error'] === 0) {
    $file_admin = $_FILES['file_admin']['name'];
    $tmp_name = $_FILES['file_admin']['tmp_name'];
    $upload_dir = "uploads/";
    $target_file = $upload_dir . basename($file_admin);

    // Hapus file lama dulu biar gak numpuk
    if (!empty($_POST['file_lama']) && file_exists($upload_dir . $_POST['file_lama'])) {
        unlink($upload_dir . $_POST['file_lama']);
    }

    if (move_uploaded_file($tmp_name, $target_file)) {
        $file_to_save = $file_admin;
    } else {
        die("Error: Gagal mengupload file.");
    }
}

    // Query update
    $sql = "UPDATE tasks SET nama_tugas=?, detail_tugas=?, ditugaskan=?, deadline=?, prioritas=?, file_admin=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssi", $nama_tugas, $detail_tugas, $ditugaskan, $deadline, $prioritas, $file_to_save, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: manager-tasks.php");
        exit();
    } else {
        die("Error: " . mysqli_stmt_error($stmt));
    }
} else {
    echo "Error: ID tidak valid.";
    exit;
}
?>
