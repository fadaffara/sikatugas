<?php
include("../config.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_id']) && isset($_FILES['file_pengumpulan'])) {
    $taskId = $_POST['task_id'];
    $uploadDir = 'upload/';
    $fileName = basename($_FILES['file_pengumpulan']['name']);
    $uploadFilePath = $uploadDir . $fileName;

    // Validasi ekstensi file
    $allowedExtensions = array("zip", "rar", "7z", "tar", "gz", "bz2", "xz");
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validasi ukuran file (maksimal 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
    $fileSize = $_FILES['file_pengumpulan']['size'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "Error: Hanya file dengan ekstensi ZIP, RAR, 7Z, TAR, GZ, BZ2, XZ yang diperbolehkan.";
    } elseif ($fileSize > $maxFileSize) {
        echo "Error: Ukuran file tidak boleh lebih dari 5MB.";
    } else {
        // Proses upload file
        if (move_uploaded_file($_FILES['file_pengumpulan']['tmp_name'], $uploadFilePath)) {
            $sql = "UPDATE tasks SET file_pengumpulan = ?, status = 'Menunggu Approval' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $fileName, $taskId);

            if ($stmt->execute()) {
                header("Location: user-tasks.php"); 
                exit;
            } else {
                echo "Error: Tidak dapat memperbarui database.";
            }
            $stmt->close();
        } else {
            echo "Gagal Mengupload File.";
        }
    }
}
$conn->close();
?>
