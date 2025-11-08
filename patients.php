<?php
include('db_connect.php');

// --- Add Patient ---
if (isset($_POST['add_patient'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $disease = $_POST['disease'];
    $severity = $_POST['severity'];
    $selected_bed = $_POST['bed_id'];

    // Insert patient first (without bed_id)
    $conn->query("INSERT INTO patients (name, age, gender, contact, address, disease, severity) 
                  VALUES ('$name','$age','$gender','$contact','$address','$disease','$severity')");
    $patient_id = $conn->insert_id;

    if ($selected_bed) {
        // Use manually selected bed
        $bed_id = $selected_bed;
        $conn->query("UPDATE beds SET status='Occupied', patient_id=$patient_id WHERE bed_id=$bed_id");
        $conn->query("UPDATE patients SET bed_id=$bed_id WHERE patient_id=$patient_id");
    } else {
        // Auto assign based on severity
        $bed_type = ($severity == 'High') ? 'ICU' : 'General';
        $bed_query = $conn->query("SELECT bed_id FROM beds WHERE status='Available' AND type='$bed_type' LIMIT 1");
        if ($bed_query && $bed_query->num_rows > 0) {
            $bed = $bed_query->fetch_assoc();
            $bed_id = $bed['bed_id'];
            $conn->query("UPDATE beds SET status='Occupied', patient_id=$patient_id WHERE bed_id=$bed_id");
            $conn->query("UPDATE patients SET bed_id=$bed_id WHERE patient_id=$patient_id");
        } else {
            $bed_id = NULL;
            echo "<script>alert('⚠ No $bed_type bed available!');</script>";
        }
    }

    header("Location: patients.php");
    exit;
}

// --- Assign Bed to Existing Patient ---
if(isset($_POST['assign_bed_btn'])){
    $patient_id = $_POST['patient_id'];
    $bed_id = $_POST['assign_bed'];

    // Update bed as occupied
    $conn->query("UPDATE beds SET status='Occupied', patient_id=$patient_id WHERE bed_id=$bed_id");
    // Update patient with bed_id
    $conn->query("UPDATE patients SET bed_id=$bed_id WHERE patient_id=$patient_id");

    header("Location: patients.php");
    exit;
}

// --- Delete Patient ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Free the bed if assigned
    $bed_query = $conn->query("SELECT bed_id FROM patients WHERE patient_id=$id");
    $bed = $bed_query->fetch_assoc();
    if ($bed['bed_id']) {
        $conn->query("UPDATE beds SET status='Available', patient_id=NULL WHERE bed_id=" . $bed['bed_id']);
    }
    $conn->query("DELETE FROM patients WHERE patient_id=$id");
    header("Location: patients.php");
    exit;
}

// --- Fetch All Patients ---
$result = $conn->query("
    SELECT p.*, b.bed_number, b.type AS bed_type 
    FROM patients p 
    LEFT JOIN beds b ON p.bed_id = b.bed_id 
    ORDER BY p.patient_id DESC
") or die("SQL Error: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Patients</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<h1 class="text-3xl font-bold text-center text-blue-600 mb-6">👨‍⚕️ Manage Patients</h1>

<!-- Add Patient Form -->
<form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Patient</h2>
    <input type="text" name="name" placeholder="Name" required class="w-full p-2 mb-3 border rounded">
    <input type="number" name="age" placeholder="Age" required class="w-full p-2 mb-3 border rounded">
    <select name="gender" required class="w-full p-2 mb-3 border rounded">
        <option value="">Select Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>
    <input type="text" name="contact" placeholder="Contact" class="w-full p-2 mb-3 border rounded">
    <textarea name="address" placeholder="Address" class="w-full p-2 mb-3 border rounded"></textarea>
    <input type="text" name="disease" placeholder="Disease / Diagnosis" class="w-full p-2 mb-3 border rounded">
    <select name="severity" required class="w-full p-2 mb-3 border rounded">
        <option value="">Select Severity</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
    </select>

    <!-- Manual Bed Assignment -->
    <select name="bed_id" class="w-full p-2 mb-3 border rounded">
        <option value="">Auto Assign</option>
        <?php
        $beds = $conn->query("SELECT bed_id, bed_number, type FROM beds WHERE status='Available'");
        while ($b = $beds->fetch_assoc()) {
            echo "<option value='{$b['bed_id']}'>{$b['bed_number']} ({$b['type']})</option>";
        }
        ?>
    </select>

    <button type="submit" name="add_patient" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Add Patient
    </button>
</form>

<!-- Patient List -->
<div class="bg-white p-6 rounded-2xl shadow-md max-w-6xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Patient Records</h2>
    <table class="w-full border-collapse text-center">
        <tr class="bg-blue-100">
            <th class="border p-2">ID</th>
            <th class="border p-2">Name</th>
            <th class="border p-2">Age</th>
            <th class="border p-2">Gender</th>
            <th class="border p-2">Contact</th>
            <th class="border p-2">Address</th>
            <th class="border p-2">Disease</th>
            <th class="border p-2">Severity</th>
            <th class="border p-2">Bed</th>
            <th class="border p-2">Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td class="border p-2"><?= $row['patient_id'] ?></td>
            <td class="border p-2"><?= $row['name'] ?></td>
            <td class="border p-2"><?= $row['age'] ?></td>
            <td class="border p-2"><?= $row['gender'] ?></td>
            <td class="border p-2"><?= $row['contact'] ?></td>
            <td class="border p-2"><?= $row['address'] ?></td>
            <td class="border p-2"><?= $row['disease'] ?></td>
            <td class="border p-2"><?= $row['severity'] ?></td>
            <td class="border p-2">
                <?php if(!$row['bed_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="patient_id" value="<?= $row['patient_id'] ?>">
                        <select name="assign_bed" required class="p-1 border rounded">
                            <option value="">Select Bed</option>
                            <?php
                            $beds = $conn->query("SELECT bed_id, bed_number, type FROM beds WHERE status='Available'");
                            while($b = $beds->fetch_assoc()){
                                echo "<option value='{$b['bed_id']}'>{$b['bed_number']} ({$b['type']})</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="assign_bed_btn" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Assign</button>
                    </form>
                <?php else: ?>
                    <?= $row['bed_number']." (".$row['bed_type'].")" ?>
                <?php endif; ?>
            </td>
            <td class="border p-2">
                <a href="?delete=<?= $row['patient_id'] ?>" class="text-red-600 font-semibold hover:underline" onclick="return confirm('Delete this record?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="text-center mt-6">
    <a href="index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
</div>

</body>
</html>
