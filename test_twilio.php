<?php
require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

$sid = 'ACfd8978c26a712ac4a99f8b07b5251d14';
$token = 'd4833a6478450dcbc5145e2b65b1abdd';
$twilio = new Client($sid, $token);

try {
    $message = $twilio->messages->create(
        '+63991744XXXX', // <- Replace with your verified phone number
        [
            'from' => '+14484086249', // <- Your Twilio phone number
            'body' => 'Hello! This is a test SMS from Attendify 📱'
        ]
    );

    echo "✅ Message sent successfully! SID: " . $message->sid;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
