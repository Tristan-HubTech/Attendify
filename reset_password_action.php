<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['password_reset_user']) || !isset($_SESSION['password_reset_allowed']) || $_SESSION['password_reset_allowed'] < time()) {
        die('Session expired. Start again.');
    }

    $uid = $_SESSION['password_reset_user'];
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (empty($pw) || $pw !== $pw2) die('Passwords do not match or empty.');
    if (strlen($pw) < 6) die('Password too short (min 6 chars).');

    $hash = password_hash($pw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password_hash=?, otp_code=NULL, otp_expires=NULL, otp_verified=0 WHERE id=?");
    $stmt->bind_param("si", $hash, $uid);
    $stmt->execute();

    unset($_SESSION['password_reset_user']);
    unset($_SESSION['password_reset_allowed']);

    echo 'Password updated. <a href="login.php">Login</a>';
}
?>
