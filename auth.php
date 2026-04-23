<?php
session_start();

// Redirect to login if perfectly not logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit;
}

// Function to check if user has required role
function check_role($allowed_roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    // If it's a string, make it an array for easier checking
    if (is_string($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    return in_array($_SESSION['role'], $allowed_roles);
}

// Function to enforce access (redirects if not allowed)
function enforce_access($allowed_roles) {
    if (!check_role($allowed_roles)) {
        echo "<script>alert('Access Denied. You do not have permission to view this page.'); window.location.href='index.php';</script>";
        exit;
    }
}
?>
