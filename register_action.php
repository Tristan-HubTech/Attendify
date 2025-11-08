<?php
// register_action.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$student_phone = trim($_POST['student_phone'] ?? '');
$parent_phone = trim($_POST['parent_phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['password2'] ?? '');
$role = trim($_POST['role'] ?? 'student');

// Basic validation
if ($email === '' || $student_phone === '' || $parent_phone === '' || $password === '' || $confirm === '') {
    die("Please fill in all required fields.");
}
if ($password !== $confirm) {
    die("Passwords do not match.");
}
// simple phone validation (PH)
if (!preg_match('/^(?:\+63|0)\d{10}$/', $student_phone) || !preg_match('/^(?:\+63|0)\d{10}$/', $parent_phone)) {
    die("Please enter valid Philippine phone numbers (e.g. +639XXXXXXXXX or 09XXXXXXXXX).");
}

// check for existing email
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$check) die("DB prepare error (check): " . $conn->error);
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    die("That email is already registered. Try logging in.");
}
$check->close();

// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Make sure your users table has exactly these columns; adjust below if yours differ.
$sql = "INSERT INTO users (email, password_hash, student_phone, guardian_phone, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("DB prepare failed: " . $conn->error); // shows error if a column is missing
}

$stmt->bind_param("sssss", $email, $hash, $student_phone, $parent_phone, $role);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo "<script>alert('Registration successful as {$role}! Please login.'); window.location.href='login.php';</script>";
    exit();
} else {
    die("DB execute failed: " . $stmt->error);
}
