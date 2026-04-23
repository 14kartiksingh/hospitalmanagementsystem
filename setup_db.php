<?php
include 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL,
    reference_id INT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Insert default Admin account
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$insertAdmin = "INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$password_hash', 'admin')";
if ($conn->query($insertAdmin) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "Default admin 'admin' with password 'admin123' created successfully.\n";
    } else {
         echo "Default admin already exists.\n";
    }
} else {
     echo "Error creating default admin: " . $conn->error . "\n";
}
?>
