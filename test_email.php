<?php
require 'config.php';

$result = sendEmail('youremail@example.com', 'Test Email from Attendify', '<h3>Hello Tristan!</h3><p>This is a test email.</p>');

if ($result === true) {
    echo "<h2 style='color:green;'>✅ Email sent successfully!</h2>";
} else {
    echo "<h2 style='color:red;'>❌ $result</h2>";
}
?>
