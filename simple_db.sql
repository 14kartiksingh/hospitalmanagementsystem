CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  PRIMARY KEY (`appointment_id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
);

CREATE TABLE `beds` (
  `bed_id` int(11) NOT NULL AUTO_INCREMENT,
  `bed_number` varchar(10) DEFAULT NULL,
  `status` enum('Available','Occupied') DEFAULT 'Available',
  `patient_id` int(11) DEFAULT NULL,
  `type` enum('General','ICU') DEFAULT 'General',
  PRIMARY KEY (`bed_id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE SET NULL
);

INSERT INTO `beds` VALUES (8,'B101','Occupied',43,'General'),(9,'B102','Available',NULL,'General'),(10,'B103','Available',NULL,'General'),(11,'B104','Available',NULL,'General'),(12,'B105','Available',NULL,'General'),(13,'ICU-1','Available',NULL,'ICU'),(14,'ICU-2','Available',NULL,'ICU'),(15,'ICU-3','Available',NULL,'ICU'),(16,'ICU-4','Available',NULL,'ICU'),(17,'ICU-5','Available',NULL,'ICU'),(18,'4535','Available',NULL,'General');

CREATE TABLE `discharge_summaries` (
  `summary_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `total_bill` decimal(10,2) DEFAULT 0.00,
  `medicines` text DEFAULT NULL,
  `bed_charge` decimal(10,2) DEFAULT 0.00,
  `doctor_fee` decimal(10,2) DEFAULT 0.00,
  `patient_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`summary_id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
);

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`doctor_id`)
);

INSERT INTO `doctors` VALUES (3,'Dr. Arjun Mehta','Cardiologist','9876543210','arjun.mehta@hospital.com'),(4,'Dr. Riya Kapoor','Neurologist','9898989898','riya.kapoor@hospital.com'),(5,'Dr. Karan Singh','Orthopedic','9988776655','karan.singh@hospital.com'),(6,'Dr. Neha Sharma','Pediatrician','9786541230','neha.sharma@hospital.com'),(7,'Dr. Rajat Verma','General Physician','9001122334','rajat.verma@hospital.com'),(9,'Dr. Smith','Cardiology','1234567890','smith@hospital.com');

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`medicine_id`)
);

INSERT INTO `medicines` VALUES (2,'Paracetamol','Tablet',100,'2026-12-31',1.50),(3,'Amoxicillin','Capsule',0,'2025-11-30',2.00),(4,'Cough Syrup','Syrup',0,'2025-10-15',5.00),(5,'Ibuprofen','Tablet',0,'2027-01-20',1.75),(6,'Vitamin C','Tablet',180,'2026-05-31',0.50),(8,'Metformin','Tablet',60,'2026-03-31',3.00),(9,'Antacid','Syrup',0,'2026-02-28',2.50),(10,'Omeprazole','Capsule',60,'2026-06-15',2.20),(11,'Hydrocortisone','Ointment',0,'2025-11-20',4.50),(12,'dolo','tablet',0,'2008-12-12',7.00);

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `disease` varchar(255) DEFAULT NULL,
  `severity` enum('Low','Medium','High') DEFAULT NULL,
  `bed_id` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`patient_id`)
);

INSERT INTO `patients` VALUES (43,'Kartik',20,'092384','Low',8,'Male','23094','0934','2026-04-24 02:04:47'),(44,'John Doe',30,'Flu','Low',NULL,'Male','0987654321','123 Main St','2026-04-24 02:05:52');

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `shift_time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`staff_id`)
);

INSERT INTO `staff` VALUES (3,'Amit Kumar','Nurse','9876501234','Morning'),(4,'Priya Sharma','Receptionist','9898012345','Evening'),(5,'Rohit Gupta','Lab Technician','9765432189','Morning'),(6,'Sneha Patel','Ward Boy','9845612378','Night'),(7,'Vikram Joshi','Pharmacist','9723456789','Evening');

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

INSERT INTO `users` VALUES (1,'admin','$2y$10$xKg.xDUI3pa5Bul4ulNpI.pVTckJ5/O1pf3xPjqYXsjCCEKs1nu8i','admin',NULL),(2,'drsmith','$2y$10$dz75Gqyti7.7kwEoshkw8OQaHUGUdYn7rUjlB9.SbMu48Ya6b30b.','doctor',9),(3,'johndoe','$2y$10$ZbbrT.7SlnMuv6hd.y4Om./yqulujHxn./keRSK/s.gMW9qSpgQuy','patient',44);
