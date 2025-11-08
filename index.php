<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hospital Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <h1 class="text-4xl font-bold text-center text-blue-600 mb-10">🏥 Hospital Management System</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">

        <!-- Patients -->
        <a href="patients.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">👤 Patients</h2>
            <p>Manage patient records, add, update, or delete.</p>
        </a>

        <!-- Doctors -->
        <a href="doctors.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">🩺 Doctors</h2>
            <p>Manage doctors, specialties, and contact info.</p>
        </a>

        <!-- Appointments -->
        <a href="appointments.php" class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
            <h2 class="text-2xl font-semibold mb-2">📅 Appointments</h2>
            <p>Schedule, view, and delete appointments.</p>
        </a>

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

    </div>

</body>

</html>