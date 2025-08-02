<?php
$host = "localhost";        // or 127.0.0.1
$user = "root";             // default username in XAMPP
$password = "";             // default password is empty in XAMPP
$database = "attendance_db"; // your database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
