<?php
include('db_connect.php');
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: admin-dashboard/admin.php");
    exit();
} elseif (isset($_SESSION['user'])) {
    header("Location: user-dashboard/user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendify</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="main-header">
    <div class="header-content">

      <!-- Left: ACLC Logo -->
      <div class="logo-box">
        <img src="ama.png" alt="ACLC Logo" class="aclc-logo">
      </div>

      <!-- Center: Title -->
      <div class="center-title">
        <h1 class="main-title">Attendify</h1>
      </div>

      <!-- Right: Buttons -->
      <div class="button-box">
        <a href="register.php" class="reg">Register</a>
        <a href="login.php" class="log">Login</a>
      </div>

    </div>
  </header>
</body>
</html>
