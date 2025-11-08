<?php
$servername = "localhost";
$username = "root";  // default in XAMPP
$password = "";      // leave blank unless you set one
$database = "hospital_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Database connected successfully!";
?>
