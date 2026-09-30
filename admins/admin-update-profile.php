<?php
session_start();
require_once("../config.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $current_photo = isset($_POST['current_photo']) ? $_POST['current_photo'] : '';
    $new_photo = isset($_FILES['photo']) ? $_FILES['photo'] : null;
    $default_photo = "../images/photo-of-admin.jpg";
    $upload_dir = "../images/";
    $photo_name = $current_photo;

    if ($new_photo && $new_photo['error'] === UPLOAD_ERR_OK) {
        $uploaded_name = basename($new_photo['name']);
        $target_file = $upload_dir . $uploaded_name;

        if (move_uploaded_file($new_photo['tmp_name'], $target_file)) {

            if ($current_photo && $current_photo !== $default_photo && file_exists($current_photo)) {
                unlink($current_photo); 
            }
            $photo_name = $uploaded_name; 
        } else {
            echo "<script>alert('Gagal mengunggah foto profil baru!'); window.location.href = 'admin-profile.php';</script>";
            exit;
        }
    } elseif ($current_photo && $current_photo !== $default_photo && file_exists($current_photo)) {
        unlink($current_photo);
        $photo_name = $default_photo; 
    }

    $sql = "UPDATE admin SET name = ?, photo = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $photo_name);

    if ($stmt->execute()) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location.href = 'admin-profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui profil! Silakan coba lagi.'); window.location.href = 'admin-profile.php';</script>";
        exit;
    }
    $stmt->close();
    $conn->close();
}
?>
