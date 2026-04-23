CREATE DATABASE IF NOT EXISTS hospital_db;
USE hospital_db;

-- --------------------
-- PATIENTS
-- --------------------
CREATE TABLE patients (
  patient_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  age INT,
  disease VARCHAR(255),
  severity ENUM('Low','Medium','High'),
  bed_id INT,
  gender ENUM('Male','Female','Other'),
  contact VARCHAR(15),
  address TEXT,
  date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------
-- DOCTORS
-- --------------------
CREATE TABLE doctors (
  doctor_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  specialization VARCHAR(100),
  contact VARCHAR(15),
  email VARCHAR(100)
);

-- --------------------
-- BEDS
-- --------------------
CREATE TABLE beds (
  bed_id INT AUTO_INCREMENT PRIMARY KEY,
  bed_number VARCHAR(10),
  status ENUM('Available','Occupied') DEFAULT 'Available',
  patient_id INT,
  type ENUM('General','ICU') DEFAULT 'General',
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL
);

-- --------------------
-- APPOINTMENTS
-- --------------------
CREATE TABLE appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  doctor_id INT,
  appointment_date DATE,
  reason TEXT,
  status ENUM('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- --------------------
-- DISCHARGE SUMMARY
-- --------------------
CREATE TABLE discharge_summaries (
  summary_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  doctor_id INT,
  diagnosis TEXT,
  treatment TEXT,
  discharge_date DATE,
  total_bill DECIMAL(10,2) DEFAULT 0.00,
  medicines TEXT,
  bed_charge DECIMAL(10,2) DEFAULT 0.00,
  doctor_fee DECIMAL(10,2) DEFAULT 0.00,
  patient_name VARCHAR(255),
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- --------------------
-- MEDICINES
-- --------------------
CREATE TABLE medicines (
  medicine_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  type VARCHAR(50),
  quantity INT,
  expiry_date DATE,
  price DECIMAL(10,2) DEFAULT 0.00
);

-- --------------------
-- STAFF
-- --------------------
CREATE TABLE staff (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  role VARCHAR(100),
  contact VARCHAR(15),
  shift_time VARCHAR(50)
);

-- --------------------
-- USERS (LOGIN SYSTEM)
-- --------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','doctor','patient') NOT NULL,
  reference_id INT
);

-- --------------------
-- SAMPLE DATA
-- --------------------

INSERT INTO doctors (name, specialization, contact, email) VALUES
('Dr. Arjun Mehta','Cardiologist','9876543210','arjun.mehta@hospital.com'),
('Dr. Riya Kapoor','Neurologist','9898989898','riya.kapoor@hospital.com');

INSERT INTO patients (name, age, disease, severity, gender, contact, address)
VALUES ('Kartik',20,'Flu','Low','Male','9999999999','India');

INSERT INTO beds (bed_number, status, type) VALUES
('B101','Available','General'),
('ICU-1','Available','ICU');