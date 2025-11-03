<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "system_attendance"; // make sure this matches what you created

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
