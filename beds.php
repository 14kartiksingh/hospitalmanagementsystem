<?php
include('db_connect.php');

// --- Add Bed ---
if(isset($_POST['add_bed'])){
    $bed_number = $_POST['bed_number'];
    $type = $_POST['type'];
    $conn->query("INSERT INTO beds (bed_number, type, status) VALUES ('$bed_number', '$type', 'Available')");
    header("Location: beds.php");
    exit;
}

// --- Delete Bed ---
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];

    // Free any patient linked to this bed first
    $conn->query("UPDATE patients SET bed_id=NULL WHERE bed_id=$id");
    
    // Then delete the bed
    $conn->query("DELETE FROM beds WHERE bed_id=$id");
    header("Location: beds.php");
    exit;
}

// --- Fetch All Beds ---
$result = $conn->query("SELECT * FROM beds ORDER BY bed_id ASC") or die($conn->error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Beds</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<h1 class="text-3xl font-bold text-center text-blue-600 mb-6">🛏️ Manage Beds</h1>

<!-- Add Bed Form -->
<form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Bed</h2>
    <input type="text" name="bed_number" placeholder="Bed Number" required class="w-full p-2 mb-3 border rounded">
    <select name="type" required class="w-full p-2 mb-3 border rounded">
        <option value="">Select Type</option>
        <option value="General">General</option>
        <option value="ICU">ICU</option>
    </select>
    <button type="submit" name="add_bed" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Bed</button>
</form>

<!-- Bed List -->
<div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Bed Records</h2>
    <table class="w-full border-collapse text-center">
        <tr class="bg-blue-100">
            <th class="border p-2">Bed ID</th>
            <th class="border p-2">Bed Number</th>
            <th class="border p-2">Type</th>
            <th class="border p-2">Status</th>
            <th class="border p-2">Patient ID</th>
            <th class="border p-2">Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td class="border p-2"><?= $row['bed_id'] ?></td>
            <td class="border p-2"><?= $row['bed_number'] ?></td>
            <td class="border p-2"><?= $row['type'] ?></td>
            <td class="border p-2 <?= $row['status']=='Occupied'?'text-red-600':'text-green-600' ?>"><?= $row['status'] ?></td>
            <td class="border p-2"><?= $row['patient_id'] ? $row['patient_id'] : '-' ?></td>
            <td class="border p-2">
                <a href="?delete=<?= $row['bed_id'] ?>" 
                   class="text-red-600 font-semibold hover:underline" 
                   onclick="return confirm('⚠️ Are you sure you want to delete this bed?')">Delete</a>
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
