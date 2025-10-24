<?php
require 'db_connect.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('Location: verify_otp.php?email=' . urlencode($email));
        exit;
    }

    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $update = "UPDATE users SET otp_code=?, otp_expires=?, otp_verified=0 WHERE id=?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("ssi", $otp, $expires, $user['id']);
    $stmt2->execute();

    $subject = "Your OTP for Password Reset";
    $body = "<p>Your OTP code is: <strong>{$otp}</strong></p><p>It expires in 15 minutes.</p>";
    sendEmailOTP($email, $subject, $body);

    header('Location: verify_otp.php?email=' . urlencode($email));
}
?>
