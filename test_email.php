<?php
require 'config.php';

$sent = sendEmailOTP('tyasuham@gmail.com', 'Test OTP', 'This is a test email from PHPMailer.');

if ($sent) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send.";
}
