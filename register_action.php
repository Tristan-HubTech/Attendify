<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$email         = trim($_POST['email'] ?? '');
$student_phone = trim($_POST['student_phone'] ?? '');
$parent_phone  = trim($_POST['parent_phone'] ?? '');
$password      = trim($_POST['password'] ?? '');
$confirm       = trim($_POST['password2'] ?? '');
$role          = trim($_POST['role'] ?? 'student');

/* ===============================
   REQUIRED FIELDS
================================*/
if ($email === '' || $student_phone === '' || $parent_phone === '' ||
    $password === '' || $confirm === '') {

    $_SESSION['reg_error'] = "⚠️ Please fill in all required fields.";
    header("Location: register.php");
    exit();
}

/* ===============================
   STRICT EMAIL VALIDATION
   Only *@gmail.com
================================*/
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reg_error'] = "❌ Invalid email format.";
    header("Location: register.php");
    exit();
}

if (!preg_match('/@gmail\.com$/i', $email)) {
    $_SESSION['reg_error'] = "❌ Email must use @gmail.com only.";
    header("Location: register.php");
    exit();
}

/* ===============================
   PHONE VALIDATION
================================*/
if ($student_phone === $parent_phone) {
    $_SESSION['reg_error'] = "⚠️ Student and Parent phone numbers cannot be the same.";
    header("Location: register.php");
    exit();
}

$phone_pattern = '/^(?:\+63|0)9\d{9}$/'; // PH format

if (!preg_match($phone_pattern, $student_phone) ||
    !preg_match($phone_pattern, $parent_phone)) {

    $_SESSION['reg_error'] = "❌ Enter valid PH numbers (+639XXXXXXXXX or 09XXXXXXXXX).";
    header("Location: register.php");
    exit();
}

/* ===============================
   PASSWORD VALIDATION
================================*/
if ($password !== $confirm) {
    $_SESSION['reg_error'] = "❌ Passwords do not match.";
    header("Location: register.php");
    exit();
}

$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$special   = preg_match('@[^\w]@', $password);

if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
    $_SESSION['reg_error'] = "⚠️ Password must have uppercase, lowercase, number, special character, and 8+ chars.";
    header("Location: register.php");
    exit();
}

/* ===============================
   ROLE VALIDATION
================================*/
$allowed_roles = ['student','teacher','admin'];
if (!in_array($role, $allowed_roles)) {
    $_SESSION['reg_error'] = "❌ Invalid role selected.";
    header("Location: register.php");
    exit();
}

/* ===============================
   CHECK EMAIL DUPLICATE
================================*/
$checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    $_SESSION['reg_error'] = "⚠️ Email already registered.";
    header("Location: register.php");
    exit();
}
$checkEmail->close();

/* ===============================
   CHECK PHONE DUPLICATE
================================*/
$checkPhone = $conn->prepare(
    "SELECT id FROM users WHERE student_phone = ? OR guardian_phone = ?"
);
$checkPhone->bind_param("ss", $student_phone, $parent_phone);
$checkPhone->execute();
$checkPhone->store_result();

if ($checkPhone->num_rows > 0) {
    $_SESSION['reg_error'] = "⚠️ One of these phone numbers already exists.";
    header("Location: register.php");
    exit();
}
$checkPhone->close();

/* ===============================
   INSERT NEW USER
================================*/
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (email, password_hash, student_phone, guardian_phone, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $email, $hash, $student_phone, $parent_phone, $role);

if ($stmt->execute()) {
    $new_user_id = $stmt->insert_id;
    $stmt->close();

    /* ===============================
       AUTO-CREATE PROFILE
    =================================*/

    // Teacher
    if ($role === 'teacher') {
        $default_name = "Teacher $new_user_id";
        $t = $conn->prepare("INSERT INTO teacher_profiles (teacher_id, full_name) VALUES (?, ?)");
        if ($t) {
            $t->bind_param("is", $new_user_id, $default_name);
            $t->execute();
            $t->close();
        }
    }
if ($role === 'student') {
    $default_name = "Student $new_user_id";

    // Correct table columns
    $s = $conn->prepare("
        INSERT INTO students (user_id, student_name, email, parent_phone)
        VALUES (?, ?, ?, ?)
    ");

    if ($s) {
        $s->bind_param("isss", $new_user_id, $default_name, $email, $parent_phone);
        $s->execute();
        $s->close();
    }
}


    $_SESSION['reg_success'] = "✅ Registration successful! Please log in.";
    header("Location: login.php");
    exit();

} else {
    $_SESSION['reg_error'] = "❌ Error: " . $stmt->error;
    header("Location: register.php");
    exit();
}
?>
