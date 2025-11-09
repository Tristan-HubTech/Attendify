<?php
// admin_header.php - logic only, no HTML output
if (defined('ADMIN_HEADER_INCLUDED')) {
    return;
}
define('ADMIN_HEADER_INCLUDED', true);

// start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// load DB
require_once __DIR__ . '/../db_connect.php';

// ensure only admins can see admin pages
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// fetch admin name safely (fallback to 'Admin User')
$admin_name = "Admin User";
$admin_id = intval($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare("SELECT full_name FROM admin_profiles WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['full_name'])) $admin_name = $row['full_name'];
    }
    $stmt->close();
}
