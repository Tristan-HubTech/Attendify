<?php
include('db_connect.php');
session_start();

<<<<<<< HEAD

=======
// If logged in as admin, redirect to admin dashboard
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
if (isset($_SESSION['admin'])) {
    header("Location: admin-dashboard/admin.php");
    exit();
}   
<<<<<<< HEAD

=======
// If logged in as user, redirect to user dashboard
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
elseif (isset($_SESSION['user'])) {
    header("Location: user-dashboard/user.php");
    exit();
}           
?>

<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AclcTrack</title>
  <link rel="stylesheet" href="style.css">
</head>
  
<body>
<header class="main-header">
  <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
  <h1 class="main-title">Attendify</h1>
 
  <a href="register.php" class="button register-btn">Signup</a>
  <a href="login.php" class="button login-btn">Login</a>
</header>

</body>
</html>
=======
    <meta charset="UTF-8">
    <title>Attendify - Main Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Welcome to Attendify</h1>
        <p>Please choose an option:</p>
        <a href="login.php" class="button">Login</a>
        <a href="register.php" class="button">Register</a>
    </div>
</body>
</html>
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
