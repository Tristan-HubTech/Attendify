<?php
<<<<<<< HEAD

=======
// functions.php
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

require 'config.php';

function sendEmailOTP($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
<<<<<<< HEAD
      
=======
        //Server settings
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EMAIL;
<<<<<<< HEAD
        $mail->Password   = SMTP_APP_PASSWORD; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        
        $mail->setFrom(SMTP_EMAIL, 'YourAppName');
        $mail->addAddress($toEmail);

        
=======
        $mail->Password   = SMTP_APP_PASSWORD; // keep this secret
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom(SMTP_EMAIL, 'YourAppName');
        $mail->addAddress($toEmail);

        // Content
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
<<<<<<< HEAD
        
=======
        // In production, log $mail->ErrorInfo somewhere safe
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
        return false;
    }
}
