<?php 
require_once ("../config.php");
session_start();
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-admin.php"); 
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    
    $queryGetDivisi = "SELECT divisi_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($queryGetDivisi);
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $divisi_id = $user['divisi_id'];

        $updateDivisiQuery = "UPDATE divisi SET jumlah_anggota = jumlah_anggota - 1 WHERE kode_divisi = ?";
        $stmt = $conn->prepare($updateDivisiQuery);
        $stmt->bind_param("s", $divisi_id); 
        $stmt->execute();

        $deleteUserQuery = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($deleteUserQuery);
        $stmt->bind_param("i", $id); 
        if ($stmt->execute()) {
            header("Location: admin-data-of-user.php?msg=User berhasil dihapus");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "User tidak ditemukan.";
    }
} else {
    header("Location: admin-data-of-user.php");
}
?>
