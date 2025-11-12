<?php
// ==========================================
// Attendify - Secure Register Action (FINAL)
// ==========================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// ============================
// 🧱 Sanitize Input
// ============================
$email         = trim($_POST['email'] ?? '');
$student_phone = trim($_POST['student_phone'] ?? '');
$parent_phone  = trim($_POST['parent_phone'] ?? '');
$password      = trim($_POST['password'] ?? '');
$confirm       = trim($_POST['password2'] ?? '');
$role          = trim($_POST['role'] ?? 'student');

// ============================
// 🚨 Validation
// ============================
if ($email === '' || $student_phone === '' || $parent_phone === '' || $password === '' || $confirm === '') {
    $_SESSION['reg_error'] = "⚠️ Please fill in all required fields.";
    header("Location: register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reg_error'] = "❌ Invalid email format.";
    header("Location: register.php");
    exit();
}

if ($password !== $confirm) {
    $_SESSION['reg_error'] = "❌ Passwords do not match.";
    header("Location: register.php");
    exit();
}

$phone_pattern = '/^(?:\+63|0)9\d{9}$/';
if (!preg_match($phone_pattern, $student_phone) || !preg_match($phone_pattern, $parent_phone)) {
    $_SESSION['reg_error'] = "❌ Please enter valid Philippine numbers (+639XXXXXXXXX or 09XXXXXXXXX).";
    header("Location: register.php");
    exit();
}

// Strong Password
$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$special   = preg_match('@[^\w]@', $password);

if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
    $_SESSION['reg_error'] = "❌ Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    header("Location: register.php");
    exit();
}

$allowed_roles = ['student', 'teacher', 'admin'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['reg_error'] = "❌ Invalid role selected.";
    header("Location: register.php");
    exit();
}

// ============================
// 🔍 Check for existing email
// ============================
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['reg_error'] = "⚠️ That email is already registered. Try logging in.";
    header("Location: register.php");
    exit();
}
$check->close();

// ============================
// 🔐 Insert into database
// ============================
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (email, password_hash, student_phone, guardian_phone, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $email, $hash, $student_phone, $parent_phone, $role);

if ($stmt->execute()) {
    $new_user_id = $stmt->insert_id;
    $stmt->close();

    // Auto create teacher/student profiles
    if ($role === 'teacher') {
        $t = $conn->prepare("INSERT INTO teacher_profiles (teacher_id, full_name) VALUES (?, ?)");
        $default_name = 'Teacher ' . $new_user_id;
        $t->bind_param("is", $new_user_id, $default_name);
        $t->execute();
        $t->close();
    } elseif ($role === 'student') {
        $s = $conn->prepare("INSERT INTO students (user_id, student_name, phone, email) VALUES (?, ?, ?, ?)");
        $default_name = 'Student ' . $new_user_id;
        $s->bind_param("isss", $new_user_id, $default_name, $student_phone, $email);
        $s->execute();
        $s->close();
    }

    $_SESSION['reg_success'] = "✅ Registration successful as {$role}! Please log in.";
    header("Location: login.php");
    exit();

} else {
    $_SESSION['reg_error'] = "❌ Database error: " . $stmt->error;
    header("Location: register.php");
    exit();
}
?>
