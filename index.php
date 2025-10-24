<?php
include('db_connect.php');
session_start();


if (isset($_SESSION['admin'])) {
    header("Location: admin-dashboard/admin.php");
    exit();
}   

elseif (isset($_SESSION['user'])) {
    header("Location: user-dashboard/user.php");
    exit();
}           
?>

<!DOCTYPE html>
<html lang="en">
<head>
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