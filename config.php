<?php 
date_default_timezone_set('Asia/Jakarta');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sikatugas"; 
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>

