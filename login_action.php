<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($pw)) {
        die('Please fill in both email and password.');
    }

    // Prepare query
    $sql = "SELECT id, password_hash FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

if ($user && password_verify($pw, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    $_SESSION['teacher'] = $email; // ✅ Add this line
    session_regenerate_id(true);

        // Redirect automatically
        echo "<script>
                alert('Login successful!');
                window.location.href = 'teacher-dashboard/attendance.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Invalid email or password.');
                window.location.href = 'login.php';
              </script>";
        exit;
    }
}
?>
