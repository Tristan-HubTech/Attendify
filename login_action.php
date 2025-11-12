<?php
session_start();
require 'db_connect.php';
require 'log_activity.php';

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

        // ✅ Verify password
        if (password_verify($password, $user['password_hash'])) {

            // ✅ Store basic session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // ✅ Role-based session setup
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];

                log_activity($conn, $user['id'], 'admin', 'Login', 'Admin logged in successfully');
                header("Location: admin-dashboard/admin.php");
                exit();

            } elseif ($user['role'] === 'teacher') {

                // 🔍 Fetch teacher_id from teacher_profiles (if linked)
                $teacher_query = $conn->prepare("SELECT teacher_id FROM teacher_profiles WHERE teacher_id = ? OR full_name = (SELECT full_name FROM users WHERE id = ?)");
                $teacher_query->bind_param("ii", $user['id'], $user['id']);
                $teacher_query->execute();
                $teacher_result = $teacher_query->get_result();
                $teacher = $teacher_result->fetch_assoc();

                if ($teacher) {
                    $_SESSION['teacher_id'] = $teacher['teacher_id'];
                } else {
                    $_SESSION['teacher_id'] = $user['id']; // fallback
                }

                log_activity($conn, $user['id'], 'teacher', 'Login', 'Teacher logged in successfully');
                header("Location: teacher-dashboard/attendance.php");
                exit();

            } elseif ($user['role'] === 'student') {

                // 🔍 Fetch student_id from students table
                $student_query = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
                $student_query->bind_param("i", $user['id']);
                $student_query->execute();
                $student_result = $student_query->get_result();
                $student = $student_result->fetch_assoc();

                if ($student) {
                    $_SESSION['student_id'] = $student['id'];
                } else {
                    $_SESSION['student_id'] = $user['id']; // fallback
                }

                log_activity($conn, $user['id'], 'student', 'Login', 'Student logged in successfully');
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
