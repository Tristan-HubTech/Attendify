<?php
require 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Fetch user from database
    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $email;

            // ✅ Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin-dashboard/admin.php");
                    exit();
                case 'teacher':
                    header("Location: teacher-dashboard/attendance.php");
                    exit();
                case 'student':
                    header("Location: students-dashboard/student_dashboard.php");
                    exit();
                default:
                    header("Location: login.php");
                    exit();
            }
        }
    }

    // ❌ Invalid credentials
    $_SESSION['login_error'] = "❌ Invalid email or password.";
    header("Location: login.php");
    exit();
}
?>
