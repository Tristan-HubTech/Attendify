<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    // ⚠️ Empty OTP check
    if (empty($otp)) {
        $_SESSION['otp_error'] = '⚠️ Please enter your OTP.';
        header("Location: verify_otp.php");
        exit();
    }

    // 🟣 Look up OTP in database
    $stmt = $conn->prepare("SELECT email, reset_expires FROM users WHERE reset_otp = ?");
    if (!$stmt) {
        error_log('Database prepare failed: ' . $conn->error);
        $_SESSION['otp_error'] = '⚠️ Server error. Please try again later.';
        header("Location: verify_otp.php");
        exit();
    }

    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ OTP found
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $expiry = strtotime($user['reset_expires']);

        // ✅ Check if still valid
        if ($expiry && $expiry > time()) {

            // ✅ Save session for password reset access
            $_SESSION['password_reset_user'] = $user['email'];
            $_SESSION['password_reset_allowed'] = time() + (15 * 60); // 15-minute access

            // 🧹 Clear OTP (for security)
            $clear = $conn->prepare("UPDATE users SET reset_otp = NULL, reset_expires = NULL WHERE email = ?");
            $clear->bind_param("s", $user['email']);
            $clear->execute();
            $clear->close();

            // ✅ Redirect to reset password form
            $_SESSION['otp_success'] = '✅ OTP verified successfully! You can now reset your password.';
            header("Location: reset_password.php");
            exit();

        } else {
            // ⏰ Expired
            $_SESSION['otp_error'] = '⏰ Your OTP has expired. Please request a new one.';
            header("Location: verify_otp.php");
            exit();
        }

    } else {
        // ❌ Invalid OTP
        $_SESSION['otp_error'] = '❌ Invalid OTP. Please double-check your code and try again.';
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
