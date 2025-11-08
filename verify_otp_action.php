<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    // 🟡 Empty OTP check
    if (empty($otp)) {
        $_SESSION['otp_error'] = '⚠️ Please enter your OTP.';
        header("Location: verify_otp.php");
        exit();
    }

    // 🟣 Prepare query to find matching OTP
    $stmt = $conn->prepare("SELECT email, reset_expires FROM users WHERE reset_otp = ?");
    if (!$stmt) {
        error_log('Database prepare failed: ' . $conn->error);
        header("Location: unauthorized.php");
        exit();
    }

    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    // 🟢 If OTP exists in database
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $expiry = strtotime($user['reset_expires']);

        // ✅ Check if OTP is still valid
        if ($expiry && $expiry > time()) {

            // ✅ Save user session for password reset access
            $_SESSION['password_reset_user'] = $user['email'];
            $_SESSION['password_reset_allowed'] = time() + (15 * 60); // 15 minutes access

            // 🧹 Clear OTP for security
            $clear = $conn->prepare("UPDATE users SET reset_otp = NULL, reset_expires = NULL WHERE email = ?");
            $clear->bind_param("s", $user['email']);
            $clear->execute();
            $clear->close();

            // 🚀 Redirect to reset password page (no JS)
            header("Location: reset_password.php");
            exit();

        } else {
            // ⏰ OTP expired
            $_SESSION['otp_error'] = '⏰ Your OTP has expired. Please request a new one.';
            header("Location: verify_otp.php");
            exit();
        }

    } else {
        // ❌ Invalid OTP
        $_SESSION['otp_error'] = '❌ Invalid OTP. Please try again.';
        header("Location: verify_otp.php");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // 🚫 Direct access protection
    header("Location: unauthorized.php");
    exit();
}
?>
