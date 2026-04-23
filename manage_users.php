<?php
include('auth.php');
enforce_access('admin');
include('db_connect.php');

$message = '';

// Handle Create User
if (isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $reference_id = NULL;

    if ($role === 'doctor') {
        $reference_id = !empty($_POST['doctor_id']) ? $_POST['doctor_id'] : NULL;
    } elseif ($role === 'patient') {
        $reference_id = !empty($_POST['patient_id']) ? $_POST['patient_id'] : NULL;
    }

    if (($role === 'doctor' || $role === 'patient') && is_null($reference_id)) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Error: You must link a profile for Doctors and Patients.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, reference_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $password, $role, $reference_id);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>User created successfully!</div>";
        } else {
            $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Error creating user (Username might already exist).</div>";
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Protect admin from deleting themselves
    if ($id !== $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$id");
        header("Location: manage_users.php");
        exit;
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>You cannot delete your own session account here.</div>";
    }
}

// Handle Reset Password
if (isset($_POST['reset_password_btn'])) {
    $user_id_to_reset = intval($_POST['user_id']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $new_password, $user_id_to_reset);
    if ($stmt->execute()) {
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Password reset successfully!</div>";
    }
}

// Fetch Doctors and Patients for dropdowns
$doctors = $conn->query("SELECT doctor_id, name FROM doctors");
$patients = $conn->query("SELECT patient_id, name FROM patients");

// Fetch Users
$users = $conn->query("SELECT id, username, role, reference_id FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleReferenceDropdown() {
            var role = document.getElementById('role').value;
            document.getElementById('doctor_select').classList.add('hidden');
            document.getElementById('patient_select').classList.add('hidden');
            document.getElementById('doctor_id').required = false;
            document.getElementById('patient_id').required = false;

            if (role === 'doctor') {
                document.getElementById('doctor_select').classList.remove('hidden');
                document.getElementById('doctor_id').required = true;
            } else if (role === 'patient') {
                document.getElementById('patient_select').classList.remove('hidden');
                document.getElementById('patient_id').required = true;
            }
        }

        function showResetModal(userId, username) {
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('reset_username_display').innerText = username;
            document.getElementById('resetModal').classList.remove('hidden');
        }

        function hideResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }
    </script>
</head>
<body class="bg-gray-100 p-6 relative">

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">🔐 Manage Users</h1>
  
  <div class="max-w-5xl mx-auto">
      <?= $message ?>
  </div>

  <!-- Create User Form -->
  <form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
      <h2 class="text-xl font-semibold mb-4">Create New Account</h2>

      <input type="text" name="username" placeholder="Username" required class="w-full p-2 mb-3 border rounded">
      <input type="password" name="password" placeholder="Password" required class="w-full p-2 mb-3 border rounded">
      
      <select name="role" id="role" required class="w-full p-2 mb-3 border rounded" onchange="toggleReferenceDropdown()">
          <option value="">Select Role</option>
          <option value="admin">Admin</option>
          <option value="doctor">Doctor</option>
          <option value="patient">Patient</option>
      </select>

      <div id="doctor_select" class="hidden">
          <select name="doctor_id" id="doctor_id" class="w-full p-2 mb-3 border rounded">
              <option value="">Link to Doctor Profile</option>
              <?php while($d = $doctors->fetch_assoc()): ?>
                  <option value="<?= $d['doctor_id'] ?>"><?= $d['name'] ?></option>
              <?php endwhile; ?>
          </select>
      </div>

      <div id="patient_select" class="hidden">
          <select name="patient_id" id="patient_id" class="w-full p-2 mb-3 border rounded">
              <option value="">Link to Patient Profile</option>
              <?php while($p = $patients->fetch_assoc()): ?>
                  <option value="<?= $p['patient_id'] ?>"><?= $p['name'] ?></option>
              <?php endwhile; ?>
          </select>
      </div>

      <button type="submit" name="create_user" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">
          Create Account
      </button>
  </form>

  <!-- User List -->
  <div class="bg-white p-6 rounded-2xl shadow-md max-w-5xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">User Accounts</h2>
      <table class="w-full border-collapse">
          <tr class="bg-blue-100">
              <th class="border p-2">ID</th>
              <th class="border p-2">Username</th>
              <th class="border p-2">Role</th>
              <th class="border p-2">Linked Profile ID</th>
              <th class="border p-2">Actions</th>
          </tr>
          <?php while($row = $users->fetch_assoc()): ?>
          <tr class="text-center">
              <td class="border p-2"><?= $row['id'] ?></td>
              <td class="border p-2"><?= $row['username'] ?></td>
              <td class="border p-2 capitalize"><?= $row['role'] ?></td>
              <td class="border p-2"><?= $row['reference_id'] ? $row['reference_id'] : 'N/A' ?></td>
              <td class="border p-2 flex justify-center items-center space-x-4">
                  <button onclick="showResetModal(<?= $row['id'] ?>, '<?= addslashes($row['username']) ?>')" class="text-blue-600 font-semibold hover:underline">Reset Password</button>
                  <?php if($row['id'] !== $_SESSION['user_id']): ?>
                      <span>|</span><a href="?delete=<?= $row['id'] ?>" class="text-red-600 font-semibold hover:underline" onclick="return confirm('Delete this user?')">Delete</a>
                  <?php else: ?>
                      <span>|</span><span class="text-gray-400">Current User</span>
                  <?php endif; ?>
              </td>
          </tr>
          <?php endwhile; ?>
      </table>
  </div>

  <!-- Reset Password Modal -->
  <div id="resetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-2xl shadow-lg max-w-sm w-full relative">
          <button type="button" onclick="hideResetModal()" class="absolute top-2 right-4 text-gray-500 hover:text-gray-800 font-bold text-2xl">&times;</button>
          <h2 class="text-xl font-semibold mb-2">Reset Password</h2>
          <p class="mb-4 text-sm text-gray-600">Resetting for user: <strong id="reset_username_display"></strong></p>
          <form method="POST">
              <input type="hidden" name="user_id" id="reset_user_id">
              <input type="password" name="new_password" placeholder="New Password" required class="w-full p-2 mb-4 border rounded">
              <button type="submit" name="reset_password_btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full">
                  Update Password
              </button>
          </form>
      </div>
  </div>

  <div class="text-center mt-6 pb-6">
      <a href="index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
  </div>

</body>
</html>
