<?php
include('db_connect.php');

// --- Add Staff ---
if (isset($_POST['add_staff'])) {
  $name = $_POST['name'];
  $role = $_POST['role'];
  $contact = $_POST['contact'];
  $shift_time = $_POST['shift_time'];

  $conn->query("INSERT INTO staff (name, role, contact, shift_time)
                VALUES ('$name', '$role', '$contact', '$shift_time')");
  header("Location: staff.php");
  exit;
}

// --- Delete Staff ---
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM staff WHERE staff_id=$id");
  header("Location: staff.php");
  exit;
}

// --- Fetch Staff ---
$result = $conn->query("SELECT * FROM staff ORDER BY staff_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Staff</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">👷 Manage Staff</h1>

  <!-- Add Staff Form -->
  <form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Staff</h2>

    <input type="text" name="name" placeholder="Staff Name" required class="w-full p-2 mb-3 border rounded">
    <input type="text" name="role" placeholder="Role (Nurse/Receptionist etc.)" class="w-full p-2 mb-3 border rounded">
    <input type="text" name="contact" placeholder="Contact" class="w-full p-2 mb-3 border rounded">
    <input type="text" name="shift_time" placeholder="Shift Timing" class="w-full p-2 mb-3 border rounded">

    <button type="submit" name="add_staff" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      Add Staff
    </button>
  </form>

  <!-- Staff List -->
  <div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Staff Records</h2>
    <table class="w-full border-collapse">
      <tr class="bg-blue-100">
        <th class="border p-2">ID</th>
        <th class="border p-2">Name</th>
        <th class="border p-2">Role</th>
        <th class="border p-2">Contact</th>
        <th class="border p-2">Shift</th>
        <th class="border p-2">Actions</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="text-center">
        <td class="border p-2"><?= $row['staff_id'] ?></td>
        <td class="border p-2"><?= $row['name'] ?></td>
        <td class="border p-2"><?= $row['role'] ?></td>
        <td class="border p-2"><?= $row['contact'] ?></td>
        <td class="border p-2"><?= $row['shift_time'] ?></td>
        <td class="border p-2">
          <a href="?delete=<?= $row['staff_id'] ?>" class="text-red-600 font-semibold hover:underline"
             onclick="return confirm('Delete this staff member?')">Delete</a>
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
