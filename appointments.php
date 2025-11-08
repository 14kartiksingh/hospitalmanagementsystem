<?php
include('db_connect.php');

// Fetch Patients and Doctors for dropdowns
$patients = $conn->query("SELECT patient_id, name FROM patients");
$doctors = $conn->query("SELECT doctor_id, name FROM doctors");

// --- Add Appointment ---
if (isset($_POST['add_appointment'])) {
  $patient_id = $_POST['patient_id'];
  $doctor_id = $_POST['doctor_id'];
  $appointment_date = $_POST['appointment_date'];
  $reason = $_POST['reason'];

  $conn->query("INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason)
                VALUES ('$patient_id', '$doctor_id', '$appointment_date', '$reason')");
  header("Location: appointments.php");
  exit;
}

// --- Delete Appointment ---
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM appointments WHERE appointment_id=$id");
  header("Location: appointments.php");
  exit;
}

// --- Fetch All Appointments ---
$query = "SELECT a.appointment_id, p.name AS patient_name, d.name AS doctor_name,
          a.appointment_date, a.reason, a.status
          FROM appointments a
          JOIN patients p ON a.patient_id = p.patient_id
          JOIN doctors d ON a.doctor_id = d.doctor_id
          ORDER BY a.appointment_id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Appointments</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">📅 Manage Appointments</h1>

  <!-- Add Appointment Form -->
  <form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Appointment</h2>

    <select name="patient_id" required class="w-full p-2 mb-3 border rounded">
      <option value="">Select Patient</option>
      <?php while($p = $patients->fetch_assoc()): ?>
        <option value="<?= $p['patient_id'] ?>"><?= $p['name'] ?></option>
      <?php endwhile; ?>
    </select>

    <select name="doctor_id" required class="w-full p-2 mb-3 border rounded">
      <option value="">Select Doctor</option>
      <?php while($d = $doctors->fetch_assoc()): ?>
        <option value="<?= $d['doctor_id'] ?>"><?= $d['name'] ?></option>
      <?php endwhile; ?>
    </select>

    <input type="date" name="appointment_date" required class="w-full p-2 mb-3 border rounded">
    <textarea name="reason" placeholder="Reason for appointment" class="w-full p-2 mb-3 border rounded"></textarea>

    <button type="submit" name="add_appointment" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      Add Appointment
    </button>
  </form>

  <!-- Appointment List -->
  <div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Appointment Records</h2>
    <table class="w-full border-collapse">
      <tr class="bg-blue-100">
        <th class="border p-2">ID</th>
        <th class="border p-2">Patient</th>
        <th class="border p-2">Doctor</th>
        <th class="border p-2">Date</th>
        <th class="border p-2">Reason</th>
        <th class="border p-2">Status</th>
        <th class="border p-2">Actions</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="text-center">
        <td class="border p-2"><?= $row['appointment_id'] ?></td>
        <td class="border p-2"><?= $row['patient_name'] ?></td>
        <td class="border p-2"><?= $row['doctor_name'] ?></td>
        <td class="border p-2"><?= $row['appointment_date'] ?></td>
        <td class="border p-2"><?= $row['reason'] ?></td>
        <td class="border p-2"><?= $row['status'] ?></td>
        <td class="border p-2">
          <a href="?delete=<?= $row['appointment_id'] ?>" class="text-red-600 font-semibold hover:underline"
             onclick="return confirm('Delete this appointment?')">Delete</a>
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
