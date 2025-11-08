<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "system_attendance"; // make sure this matches what you created

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// ✅ Automatically ensure default admin exists
$adminEmail = 'admin@admin.com';
$adminPass = password_hash('aclcadmin@', PASSWORD_DEFAULT);
$conn->query("
    INSERT INTO users (email, password_hash, role, created_at)
    SELECT '$adminEmail', '$adminPass', 'admin', NOW()
    WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = '$adminEmail')
");

?>
