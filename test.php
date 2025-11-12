<?php
// sms_test.php — test sending SMS via SMS8.io Front API

// === CONFIGURATION ===
$apiUrl = "https://app.sms8.io/services/sendFront.php";
$apiKey = "ba176e34302a4e16687e4bb5d7c286d26dcfbe95"; // your actual API key

function sendSMS($phone, $message, $apiKey, $apiUrl) {
    // Build the query string based on SMS8.io Front API format
    $params = http_build_query([
        'key' => $apiKey,
        'number' => $phone,
        'message' => $message,
    ]);

    // Combine into full URL
    $url = $apiUrl . '?' . $params;

    // Initialize cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return "❌ CURL Error: $error";
    } else {
        return $response;
    }
}

// === Handle form submission ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    $result = sendSMS($phone, $message, $apiKey, $apiUrl);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SMS8 Test Page | Attendify</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background-color: #f4f6fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    width: 400px;
}
h2 {
    color: #17345f;
    text-align: center;
}
label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
}
input[type="text"], textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-top: 5px;
}
button {
    margin-top: 15px;
    width: 100%;
    background: #17345f;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background: #e21b23;
}
.response {
    margin-top: 15px;
    padding: 10px;
    background: #eef2f7;
    border-radius: 6px;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="container">
    <h2>📱 SMS8.io Test Page</h2>
    <form method="POST">
        <label>Phone Number (with country code):</label>
        <input type="text" name="phone" placeholder="+639XXXXXXXXX" required>

        <label>Message:</label>
        <textarea name="message" rows="3" placeholder="Enter test message..." required></textarea>

        <button type="submit">Send Test SMS</button>
    </form>

    <?php if (!empty($result)): ?>
        <div class="response">
            <strong>Response:</strong><br>
            <pre><?= htmlspecialchars($result) ?></pre>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
