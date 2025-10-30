<?php
session_start();
$error = "";

// Initialize teachers array in session if not exists
if (!isset($_SESSION['teachers'])) {
    $_SESSION['teachers'] = [];
}

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isset($_SESSION['teachers'][$username]) && $_SESSION['teachers'][$username] === $password) {
        $_SESSION['teacher'] = $username; // Save logged in teacher
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Teacher Login</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php" style="text-decoration: underline;">Register here</a></p>
</div>
</body>
</html>
