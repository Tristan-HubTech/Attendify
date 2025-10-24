<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $sql = "SELECT id, otp_code, otp_expires FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['otp_code'] !== $otp || strtotime($user['otp_expires']) < time()) {
        die("Invalid OTP or expired.");
    }

    $update = "UPDATE users SET otp_verified=1 WHERE id=?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("i", $user['id']);
    $stmt2->execute();

    $_SESSION['password_reset_user'] = $user['id'];
    $_SESSION['password_reset_allowed'] = time() + 900;

    header('Location: reset_password.php');
}
?>
