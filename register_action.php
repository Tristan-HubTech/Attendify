<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_phone = trim($_POST['student_phone'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['password2'] ?? '');

    // ✅ Check for empty fields
    if (empty($email) || empty($student_phone) || empty($parent_phone) || empty($password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    // ✅ Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // ✅ Validate Philippine phone number format (+63 or 09)
    if (!preg_match('/^(\+63|0)\d{10}$/', $student_phone) || !preg_match('/^(\+63|0)\d{10}$/', $parent_phone)) {
        die("Please enter valid Philippine phone numbers (e.g. +639123456789 or 09123456789).");
    }

    // ✅ Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Insert into your database
    $sql = "INSERT INTO users (email, password_hash, student_phone, guardian_phone, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $email, $password_hash, $student_phone, $parent_phone);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful!');
                window.location.href = 'login.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
