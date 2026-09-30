<?php 
require_once ("../config.php");
session_start();
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php"); 
    exit;
}
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $deleteUser = "DELETE FROM users WHERE id = $id";
    if (mysqli_query($conn, $deleteUser)) {
        header("Location: manager-user-list.php?msg=User berhasil dihapus");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: manager-user-list.php");
}
?>
