<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
  <link rel="stylesheet" href="login.css">
<body>
    <div class="Track">
    <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
    <h2>Welcome to Attendify</h2>
    </div>
<form method="post"` class="login_box" action="login_action.php">
    <h2>Login</h2>
    <div class="buttons">
        <div class="input-group">
    <label>Email:</label><input type="email" name="email" required></label><br>
    <label>Password:</label><input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
        </div>   
    </div>
    <p>Forgot password?<a href="request_reset.php"> Reset via OTP</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</form>

</body>
</html>
