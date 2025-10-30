<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pw = $_POST['password'] ?? '';

    $sql = "SELECT id, password_hash FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($pw, $user['password_hash'])) {
        die('Invalid email or password.');
    }

    $_SESSION['user_id'] = $user['id'];
    session_regenerate_id(true);

    echo "Logged in successfully. <a href='dashboard.php'>Go to Dashboard</a>";
}
?>
