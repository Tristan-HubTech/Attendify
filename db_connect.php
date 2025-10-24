<?php
<<<<<<< HEAD
=======
// db_connect.php
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6

$host = "localhost";
$user = "root";
$pass = "";
$db   = "System_attendance";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
