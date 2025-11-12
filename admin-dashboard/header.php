<?php
// admin_header.php - Admin session and access control (no HTML)

// ✅ Prevent multiple includes
if (defined('ADMIN_HEADER_INCLUDED')) {
    return;
}
define('ADMIN_HEADER_INCLUDED', true);

// ✅ Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Load database connection
require_once __DIR__ . '/../db_connect.php';

// ✅ Restrict to admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Safe admin info
$admin_id = intval($_SESSION['user_id']);
$admin_name = "Admin User";

// ✅ Optional: fetch name directly from `users` table
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        // You can use email or replace with full_name column if you add one later
        $admin_name = $row['email'];
    }
    $stmt->close();
}

// ✅ Ready for use in pages as `$admin_name`
?>
