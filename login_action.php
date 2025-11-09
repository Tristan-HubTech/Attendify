<?php
session_start();
require 'db_connect.php';
require 'log_activity.php';
logActivity($conn, $user['id'], $user['role'], 'Login', 'User logged in successfully');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare query
    $stmt = $conn->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
    if (!$stmt) {
        die("❌ Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password_hash'])) {

            // Store session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Prevent endless reload
            ob_clean();

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin-dashboard/admin.php");
                exit();
            } elseif ($user['role'] === 'teacher') {
                header("Location: teacher-dashboard/attendance.php");
                exit();
            } elseif ($user['role'] === 'student') {
                header("Location: students-dashboard/student_dashboard.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid user role.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "Incorrect password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Account not found.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
exit();
?>
