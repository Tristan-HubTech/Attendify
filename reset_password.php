<?php
require 'db_connect.php';
session_start();

$allowed = isset($_SESSION['password_reset_allowed']) && $_SESSION['password_reset_allowed'] >= time();
if (!isset($_SESSION['password_reset_user']) || !$allowed) {
    die('Not authorized or session expired.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set New Password</title>
</head>
<<<<<<< HEAD
<link rel="stylesheet" href="reset_password.css">
<body>
     <div class="Track">
        <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
        <h2>Welcome to Attendify</h2>
    </div>

<form method="post" class="login_box" action="reset_password_action.php">
    <h2>Set New Password</h2>
     <div class="buttons">
        <div class="input-group">
    <label>New password: </label><input type="password" name="password" required></label><br>
    <label>Confirm password: </label><input type="password" name="password2" required></label><br>
    <button type="submit">Save Password</button>
        </div>   
    </div>
=======
<body>
<h2>Set New Password</h2>
<form method="post" action="reset_password_action.php">
    <label>New password: <input type="password" name="password" required></label><br>
    <label>Confirm password: <input type="password" name="password2" required></label><br>
    <button type="submit">Save Password</button>
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
</form>
</body>
</html>
