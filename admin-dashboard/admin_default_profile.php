<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db_connect.php';

// Default values
$admin_name = "Admin User";
$admin_img = "../uploads/admins/default-admin.png"; // default profile picture

// Get admin info if logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $admin_id = $_SESSION['user_id'];

    // Fetch name and profile image from admin_profiles table
    $stmt = $conn->prepare("SELECT full_name, profile_image FROM admin_profiles WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if (!empty($row['full_name'])) {
                $admin_name = $row['full_name'];
            }

            if (!empty($row['profile_image']) && file_exists("../uploads/admins/" . $row['profile_image'])) {
                $admin_img = "../uploads/admins/" . $row['profile_image'];
            }
        }

        $stmt->close();
    }
}
?>
