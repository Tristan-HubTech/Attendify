<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $confirm = trim($_POST['password2']);

    // ✅ Check if passwords match
    if ($new_password !== $confirm) {
        die("<script>alert('❌ Passwords do not match. Try again.'); window.history.back();</script>");
    }

    // ✅ Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        die("<script>alert('❌ Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'); window.history.back();</script>");
    }

    // ✅ Hash the password
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    // ✅ Prepare SQL (check for errors)
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    if (!$stmt) {
        die("❌ SQL Prepare Failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $hash, $email);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Password reset successfully!'); window.location.href='login.php';</script>";
    } else {
        die("❌ Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>
