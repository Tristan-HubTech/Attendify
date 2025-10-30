<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die("Invalid email.");
    if (empty($phone)) die("Phone is required.");
    if (empty($pw) || $pw !== $pw2) die("Passwords do not match.");
    if (strlen($pw) < 6) die("Password must be at least 6 characters.");

   
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) die("Email is already registered.");

   
    $hash = password_hash($pw, PASSWORD_DEFAULT);

 
    $stmt2 = $conn->prepare("INSERT INTO users (email, phone, password_hash) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $email, $phone, $hash);
    if ($stmt2->execute()) {
        echo "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Registration failed: " . $conn->error;
    }
}
?>
