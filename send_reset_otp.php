<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db_connect.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $_SESSION['otp_error'] = "⚠️ Please enter your email.";
        header("Location: request_reset.php");
        exit();
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['otp_error'] = "❌ No account found with that email.";
        header("Location: request_reset.php");
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    // Save OTP and expiry to DB
    $stmt = $conn->prepare("UPDATE users SET reset_otp = ?, reset_expires = ? WHERE email = ?");
    $stmt->bind_param("sss", $otp, $expires, $email);
    $stmt->execute();

    // Send OTP via PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aclcmandaue8@gmail.com'; // ✅ your Gmail
        $mail->Password = 'iljtxvsppyhvgqfa'; // ✅ app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('aclcmandaue8@gmail.com', 'System Attendance');
        $mail->addAddress($email);
        $mail->isHTML(true);

        $mail->Subject = 'Your Verification Code';

        // Email Body
       $mail->Body = "
<div style='font-family: Arial, sans-serif; color: #333; padding: 25px; background: #f4f6fa; border-radius: 10px; border: 1px solid #ddd;'>
    <div style='background-color: #233b76; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0;'>
        <h2 style='margin: 0; font-size: 20px;'>System Attendance Verification</h2>
    </div>

    <div style='background-color: #ffffff; padding: 25px; border-radius: 0 0 8px 8px;'>
        <p style='font-size: 15px;'>Hello,</p>
        <p style='font-size: 15px; line-height: 1.6;'>
            You have requested to verify your identity for accessing the <strong>System Attendance Portal</strong>.
            Please use the following one-time password (OTP) to complete your secure login or password reset:
        </p>

        <div style='text-align: center; margin: 25px 0;'>
            <h1 style='color: #e21b23; letter-spacing: 3px; font-size: 36px;'>$otp</h1>
        </div>

        <p style='font-size: 15px; line-height: 1.6;'>
            🔒 This code will expire in <strong>15 minutes</strong> for your account’s security.
            <br><br>
            If you did not request this verification, please ignore this message or contact your system administrator immediately.
        </p>

        <br>
        <p style='font-size: 14px; color: #555;'>
            Best regards,<br>
            <strong>System Attendance Team</strong><br>
            <span style='font-size: 13px; color: #777;'>Automated Email — Please do not reply</span>
        </p>
    </div>
</div>
";
        $mail->AltBody = "Your OTP code is $otp. It will expire in 15 minutes.";

        $mail->send();

        // ✅ Set success message for next page
        $_SESSION['otp_success'] = "✅ OTP sent successfully! Check your email.";
        header("Location: verify_otp.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['otp_error'] = "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
        header("Location: request_reset.php");
        exit();
    }
}
?>
