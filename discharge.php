<?php
include('db_connect.php');

// Fetch patients for discharge
$patients = $conn->query("SELECT patient_id, name FROM patients");

// --- Add Discharge Summary ---
if (isset($_POST['add_discharge'])) {
  $patient_id = $_POST['patient_id'];
  $diagnosis = $_POST['diagnosis'];
  $treatment = $_POST['treatment'];
  $discharge_date = $_POST['discharge_date'];

  $conn->query("INSERT INTO discharge_summaries (patient_id, diagnosis, treatment, discharge_date)
                VALUES ('$patient_id', '$diagnosis', '$treatment', '$discharge_date')");
  header("Location: discharge.php");
  exit;
}

// --- Delete Summary ---
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM discharge_summaries WHERE summary_id=$id");
  header("Location: discharge.php");
  exit;
}

// --- Fetch Summaries ---
$query = "SELECT ds.summary_id, p.name AS patient_name, ds.diagnosis, ds.treatment, ds.discharge_date
          FROM discharge_summaries ds
          JOIN patients p ON ds.patient_id = p.patient_id
          ORDER BY ds.summary_id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Discharge Summaries</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">📄 Discharge Summaries</h1>

  <!-- Add Summary Form -->
  <form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Discharge Summary</h2>

    <select name="patient_id" required class="w-full p-2 mb-3 border rounded">
      <option value="">Select Patient</option>
      <?php while($p = $patients->fetch_assoc()): ?>
        <option value="<?= $p['patient_id'] ?>"><?= $p['name'] ?></option>
      <?php endwhile; ?>
    </select>

    <textarea name="diagnosis" placeholder="Diagnosis" required class="w-full p-2 mb-3 border rounded"></textarea>
    <textarea name="treatment" placeholder="Treatment Given" required class="w-full p-2 mb-3 border rounded"></textarea>
    <input type="date" name="discharge_date" required class="w-full p-2 mb-3 border rounded">

    <button type="submit" name="add_discharge" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      Add Summary
    </button>
  </form>

  <!-- Discharge Summary List -->
  <div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">All Discharge Summaries</h2>
    <table class="w-full border-collapse">
      <tr class="bg-blue-100">
        <th class="border p-2">ID</th>
        <th class="border p-2">Patient</th>
        <th class="border p-2">Diagnosis</th>
        <th class="border p-2">Treatment</th>
        <th class="border p-2">Discharge Date</th>
        <th class="border p-2">Actions</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="text-center">
        <td class="border p-2"><?= $row['summary_id'] ?></td>
        <td class="border p-2"><?= $row['patient_name'] ?></td>
        <td class="border p-2"><?= $row['diagnosis'] ?></td>
        <td class="border p-2"><?= $row['treatment'] ?></td>
        <td class="border p-2"><?= $row['discharge_date'] ?></td>
        <td class="border p-2">
          <a href="?delete=<?= $row['summary_id'] ?>" class="text-red-600 font-semibold hover:underline"
             onclick="return confirm('Delete this summary?')">Delete</a>
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
