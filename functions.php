<?php
// functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

require 'config.php';

function sendEmailOTP($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EMAIL;
        $mail->Password   = SMTP_APP_PASSWORD; // keep this secret
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom(SMTP_EMAIL, 'YourAppName');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // In production, log $mail->ErrorInfo somewhere safe
        return false;
    }
}
