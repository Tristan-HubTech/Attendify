<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';

// 🔒 Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 🚫 Prevent admin from deleting themselves
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own admin account.'); window.location.href='manage_users.php';</script>";
        exit;
    }

    // ✅ Delete user and their related records
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: manage_users.php");
exit;
?>
