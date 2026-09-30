<?php
session_start(); 
if (isset($_SESSION['login_admin'])) {
    unset($_SESSION['email_admin']); 
    unset($_SESSION['login_admin']); 
}
header("Location: login-manager.php");
exit;
?>
