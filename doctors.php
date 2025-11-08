<?php
include('db_connect.php');

// --- Add Doctor ---
if (isset($_POST['add_doctor'])) {
  $name = $_POST['name'];
  $specialization = $_POST['specialization'];
  $contact = $_POST['contact'];
  $email = $_POST['email'];

  $conn->query("INSERT INTO doctors (name, specialization, contact, email)
                VALUES ('$name', '$specialization', '$contact', '$email')");
  header("Location: doctors.php");
  exit;
}

// --- Delete Doctor ---
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM doctors WHERE doctor_id=$id");
  header("Location: doctors.php");
  exit;
}

// --- Fetch Doctors ---
$result = $conn->query("SELECT * FROM doctors ORDER BY doctor_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Doctors</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">🩺 Manage Doctors</h1>

  <!-- Add Doctor Form -->
  <form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Doctor</h2>

    <input type="text" name="name" placeholder="Doctor Name" required class="w-full p-2 mb-3 border rounded">
    <input type="text" name="specialization" placeholder="Specialization" class="w-full p-2 mb-3 border rounded">
    <input type="text" name="contact" placeholder="Contact" class="w-full p-2 mb-3 border rounded">
    <input type="email" name="email" placeholder="Email" class="w-full p-2 mb-3 border rounded">

    <button type="submit" name="add_doctor" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      Add Doctor
    </button>
  </form>

  <!-- Doctor List -->
  <div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Doctor Records</h2>
    <table class="w-full border-collapse">
      <tr class="bg-blue-100">
        <th class="border p-2">ID</th>
        <th class="border p-2">Name</th>
        <th class="border p-2">Specialization</th>
        <th class="border p-2">Contact</th>
        <th class="border p-2">Email</th>
        <th class="border p-2">Actions</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="text-center">
        <td class="border p-2"><?= $row['doctor_id'] ?></td>
        <td class="border p-2"><?= $row['name'] ?></td>
        <td class="border p-2"><?= $row['specialization'] ?></td>
        <td class="border p-2"><?= $row['contact'] ?></td>
        <td class="border p-2"><?= $row['email'] ?></td>
        <td class="border p-2">
          <a href="?delete=<?= $row['doctor_id'] ?>" class="text-red-600 font-semibold hover:underline"
             onclick="return confirm('Delete this doctor?')">Delete</a>
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
