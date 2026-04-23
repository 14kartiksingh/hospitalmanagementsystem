<?php
include 'db_connect.php';

// Create a Dummy Doctor
$conn->query("INSERT IGNORE INTO doctors (name, specialization, contact, email) VALUES ('Dr. Smith', 'Cardiology', '1234567890', 'smith@hospital.com')");
$doc_id = $conn->query("SELECT doctor_id FROM doctors WHERE email='smith@hospital.com'")->fetch_object()->doctor_id;

$doc_pass = password_hash('doctor123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password, role, reference_id) VALUES ('drsmith', '$doc_pass', 'doctor', $doc_id)");

// Create a Dummy Patient
$conn->query("INSERT IGNORE INTO patients (name, age, gender, contact, address, disease, severity) VALUES ('John Doe', 30, 'Male', '0987654321', '123 Main St', 'Flu', 'Low')");
$pat_id = $conn->query("SELECT patient_id FROM patients WHERE name='John Doe'")->fetch_object()->patient_id;

$pat_pass = password_hash('patient123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, password, role, reference_id) VALUES ('johndoe', '$pat_pass', 'patient', $pat_id)");

echo "Demo accounts created successfully!";
?>
