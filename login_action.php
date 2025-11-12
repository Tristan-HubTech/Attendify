<?php
session_start();
require 'db_connect.php';
require 'log_activity.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// ✅ 1. Validate input
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = "⚠️ Please fill in both email and password.";
    header("Location: login.php");
    exit();
}

// ✅ 2. Check if email exists
$stmt = $conn->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
if (!$stmt) {
    $_SESSION['login_error'] = "❌ Database error. Please contact admin.";
    header("Location: login.php");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    $_SESSION['login_error'] = "⚠️ Account not found. Please register first.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// ✅ 3. Verify password securely
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_error'] = "❌ Incorrect password. Please try again.";
    header("Location: login.php");
    exit();
}

// ✅ 4. Create secure session
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// ✅ 5. Role-based redirects with log_activity
switch ($user['role']) {
    case 'admin':
        $_SESSION['admin_id'] = $user['id'];
        log_activity($conn, $user['id'], 'admin', 'Login', 'Admin logged in successfully');
        header("Location: admin-dashboard/admin.php");
        exit();

    case 'teacher':
        $teacher_id = null;
        $teacher_query = $conn->prepare("SELECT teacher_id FROM teacher_profiles WHERE teacher_id = ?");
        $teacher_query->bind_param("i", $user['id']);
        $teacher_query->execute();
        $teacher_result = $teacher_query->get_result();
        if ($teacher_row = $teacher_result->fetch_assoc()) {
            $teacher_id = $teacher_row['teacher_id'];
        }
        $_SESSION['teacher_id'] = $teacher_id ?? $user['id'];
        log_activity($conn, $user['id'], 'teacher', 'Login', 'Teacher logged in successfully');
        header("Location: teacher-dashboard/attendance.php");
        exit();

    case 'student':
        $student_id = null;
        $student_query = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
        $student_query->bind_param("i", $user['id']);
        $student_query->execute();
        $student_result = $student_query->get_result();
        if ($student_row = $student_result->fetch_assoc()) {
            $student_id = $student_row['id'];
        }
        $_SESSION['student_id'] = $student_id ?? $user['id'];
        log_activity($conn, $user['id'], 'student', 'Login', 'Student logged in successfully');
        header("Location: students-dashboard/student_dashboard.php");
        exit();

    default:
        $_SESSION['login_error'] = "⚠️ Invalid user role. Contact your administrator.";
        header("Location: login.php");
        exit();
}

$conn->close();
exit();
?>
