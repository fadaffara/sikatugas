<?php
session_start();
if (!isset($_SESSION['login_admin'])) {
    header("Location: login-manager.php");
    exit;
}
require_once("../config.php");
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT file_admin FROM tasks WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $file_path = 'uploads/' . $row['file_admin'];
        if (file_exists($file_path)) {
            unlink($file_path); 
        }
        $sql = "DELETE FROM tasks WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            header("Location: manager-tasks.php"); 
            exit();
        } 
        else {
            die("Error SQL: " . mysqli_error($conn));
        }
    } 
    else {
        die("Data tidak ditemukan.");
    }
} else {
    die("ID tidak valid.");
}
?>
