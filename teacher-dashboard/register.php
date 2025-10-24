<?php
session_start();
$error = "";
$success = "";

// Initialize teachers array in session if not exists
if (!isset($_SESSION['teachers'])) {
    $_SESSION['teachers'] = [];
}

// Handle account creation
if (isset($_POST['register'])) {
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);

    if (isset($_SESSION['teachers'][$newUsername])) {
        $error = "Username already exists!";
    } else {
        $_SESSION['teachers'][$newUsername] = $newPassword;
        $success = "Account created successfully! <a href='login.php'>Login now</a>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Register Account</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Already have an account? <a href="login.php" style="text-decoration: underline;">Login</a></p>
</div>
</body>
</html>
