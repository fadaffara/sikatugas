<?php
session_start();
require_once("../config.php");


if (!isset($_SESSION['user_id'])) {
    header("Location: login-user.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $current_photo = $_POST['current_photo'] ?? '';
    $new_photo = $_FILES['photo'] ?? null;

    $upload_dir = "../images/";
    $default_photo = "photo-of-user.png"; 


    $photo_name = $current_photo;


    if ($new_photo && $new_photo['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; 

        if (in_array($new_photo['type'], $allowed_types) && $new_photo['size'] <= $max_size) {
            $uploaded_name = time() . "-" . basename($new_photo['name']);
            $target_file = $upload_dir . $uploaded_name;

            if (move_uploaded_file($new_photo['tmp_name'], $target_file)) {
          
                if (!empty($current_photo) && $current_photo !== $default_photo && file_exists($upload_dir . $current_photo)) {
                    unlink($upload_dir . $current_photo);
                }
                $photo_name = $uploaded_name;
            } else {
                echo "<script>alert('Gagal mengunggah foto!'); window.location.href = 'user-profile.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('File tidak valid atau terlalu besar!'); window.location.href = 'user-profile.php';</script>";
            exit;
        }
    }


    $sql = "UPDATE users SET name = ?, username = ?, foto = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $username, $photo_name, $user_id);

  
    if ($stmt->execute()) {
        $_SESSION['name_user'] = $name;
        $_SESSION['username_user'] = $username;
        $_SESSION['photo_user'] = $photo_name; 
        $_SESSION['last_updated'] = time(); 
        echo "<script>alert('Profil berhasil diperbarui!'); window.location.href = 'user-profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui profil!'); window.location.href = 'user-profile.php';</script>";
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
