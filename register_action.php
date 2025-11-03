<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_phone = trim($_POST['student_phone'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['password2'] ?? '');
    $role = trim($_POST['role'] ?? 'student');

    if ($email === '' || $student_phone === '' || $parent_phone === '' || $password === '' || $confirm_password === '') {
        die("All fields are required.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    if (!preg_match('/^(\+63|0)\d{10}$/', $student_phone) || !preg_match('/^(\+63|0)\d{10}$/', $parent_phone)) {
        die("Please enter valid Philippine phone numbers (e.g. +639123456789 or 09123456789).");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password_hash, student_phone, guardian_phone, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $email, $password_hash, $student_phone, $parent_phone, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful as $role!');
                window.location.href = 'login.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>