<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'db_connect.php';

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role, reference_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['reference_id'] = $user['reference_id'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'User not found';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Hospital Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-sm w-full">
        <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">🏥 HMS Login</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-center"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required class="w-full p-3 mb-4 border rounded">
            <input type="password" name="password" placeholder="Password" required class="w-full p-3 mb-6 border rounded">
            <button type="submit" name="login" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700 font-bold">Login</button>
        </form>
    </div>
</body>
</html>
