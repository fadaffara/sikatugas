<?php
include("../config.php");
session_start();

if (!isset($_SESSION['login_user'])) {
    header("Location: login-user.php");
    exit;
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $task_id = $_POST['id'];
    $new_status = $_POST['status'];

   
    $sql = "UPDATE tasks SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $task_id);

    if ($stmt->execute()) {
      
        header("Location: user-tasks.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
