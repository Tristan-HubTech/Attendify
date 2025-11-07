<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';

    if ($email === '' || $pw === '') {
        header("Location: login.php?error=empty_fields");
        exit;
    }

    $sql = "SELECT id, email, password_hash, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($pw, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header("Location: admin-dashboard/admin.php");
                break;
            case 'teacher':
                header("Location: teacher-dashboard/attendance.php");
                break;
            default:
                header("Location: students-dashboard/index.php");
                break;
        }
        exit;
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}
?>
