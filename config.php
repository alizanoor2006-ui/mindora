<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // default XAMPP password is empty
$db_name = 'mindora_db';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
