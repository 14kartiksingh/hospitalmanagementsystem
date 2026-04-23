<?php
include 'auth.php';
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hospital Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto flex justify-between items-center mb-10">
        <h1 class="text-4xl font-bold text-blue-600">🏥 Hospital Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-gray-700 font-semibold">Role: <?= ucfirst($role) ?></span>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded shadow hover:bg-red-600">Logout</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">

        <?php if (in_array($role, ['admin', 'doctor'])): ?>
        <!-- Patients -->
        <a href="patients.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">👤 Patients</h2>
            <p><?= $role === 'admin' ? 'Manage patient records, add, update, or delete.' : 'View patient records and assignments.' ?></p>
        </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
        <!-- Doctors -->
        <a href="doctors.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">🩺 Doctors</h2>
            <p>Manage doctors, specialties, and contact info.</p>
        </a>
        <?php endif; ?>

        <!-- Appointments -->
        <a href="appointments.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">📅 Appointments</h2>
            <p><?= $role === 'admin' ? 'Schedule, view, and delete appointments.' : 'View appointments.' ?></p>
        </a>

        <?php if ($role === 'admin'): ?>
        <!-- Medicines -->
        <a href="medicines.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">💊 Medicines</h2>
            <p>Manage medicine inventory and expiry.</p>
        </a>

        <!-- Staff -->
        <a href="staff.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">👷 Staff</h2>
            <p>Manage hospital staff and shifts.</p>
        </a>

        <!-- Beds -->
        <a href="beds.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">🛏️ Beds</h2>
            <p>Track bed availability and assign patients.</p>
        </a>

        <!-- Discharge Summaries -->
        <a href="discharge.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">📄 Discharge Summaries</h2>
            <p>Create and view patient discharge reports.</p>
        </a>

        <!-- Manage Users -->
        <a href="manage_users.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition border-2 border-blue-200">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">🔐 Manage Users</h2>
            <p>Create accounts, manage access, and reset passwords.</p>
        </a>
        <?php endif; ?>

    </div>

</body>

</html>