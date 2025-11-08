<?php
require 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Fetch user from database
    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
    if (!$stmt) {
        die("❌ Database error: " . $conn->error);
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
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "❌ Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "❌ No account found with that email.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
