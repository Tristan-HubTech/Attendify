<?php
<<<<<<< HEAD
=======
// config.php
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

define('SMTP_EMAIL', 'tyasuham@gmail.com');
<<<<<<< HEAD
define('SMTP_APP_PASSWORD', 'ymxqoywibfhvmupf'); // your app password
=======
define('SMTP_APP_PASSWORD', 'xxxxxxxx'); // your app password
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6

function sendEmailOTP($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EMAIL;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_EMAIL, 'System Attendance');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
