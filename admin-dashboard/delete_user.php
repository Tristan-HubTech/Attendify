<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';

// 🔒 Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 🚫 Prevent admin from deleting their own account
    if ($id === $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own admin account.'); window.location.href='manage_users.php';</script>";
        exit;
    }

    // ✅ Fetch email for logging
    $email = null;
    $fetch = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $fetch->bind_param("i", $id);
    $fetch->execute();
    $fetch->bind_result($email);
    $fetch->fetch();
    $fetch->close();

    // ✅ Delete user record
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        log_activity($conn, $_SESSION['user_id'], 'admin', 'Delete User', "Deleted user: " . ($email ?: "Unknown (ID $id)"));
    }
    $stmt->close();
}

// ✅ Redirect back to Manage Users
header("Location: manage_users.php");
exit;
?>
