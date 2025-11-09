<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "system_attendance"; // make sure this matches what you created

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function logActivity($conn, $user_id, $role, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, role, action, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $role, $action, $details);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Activity log insert failed: " . $conn->error);
    }
}


?>
