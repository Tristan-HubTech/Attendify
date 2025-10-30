<?php
include('db_connect.php');
session_start();

$phone = $_GET['phone'] ?? '';

if (!$phone) {
    echo "No phone specified. Use ?phone=+63917xxxxxxx or go through send_otp.php first.";
    exit;
}

if (isset($_SESSION['reset_phone']) && $_SESSION['reset_phone'] !== $phone) {
    echo "You are not allowed to view this inbox. Start from the reset flow.";
    exit;
}

$stmt = $conn->prepare("SELECT id, body, created_at, delivered FROM sms_messages WHERE phone = ? ORDER BY created_at DESC");
$stmt->bind_param('s', $phone);
$stmt->execute();
$res = $stmt->get_result();
$messages = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head><title>Simulated SMS Inbox</title></head>
<body>
<h2>Simulated SMS Inbox for <?php echo htmlspecialchars($phone); ?></h2>

<?php if (empty($messages)): ?>
    <p>No messages found.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Time</th><th>Message</th><th>Delivered</th></tr>
        <?php foreach ($messages as $m): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['created_at']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($m['body'])); ?></td>
                <td><?php echo $m['delivered'] ? 'Yes' : 'No'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p>
  <a href="send_otp.php">Back to Send OTP</a> |
  <a href="verify_otp.php">Go to Verify OTP</a>
</p>
</body>
</html>
