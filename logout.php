<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/log_activity.php';
log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Login', 'User logged in successfully.');


// Log user logout before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    log_activity($conn, $user_id, $role, 'Logout', 'User logged out successfully.');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header("Location: login.php");
exit();
?>
