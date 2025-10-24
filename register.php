<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<<<<<<< HEAD
<link rel="stylesheet" href="register.css">
<body>
    <div class="Track">
        <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
        <h2>Welcome to Attendify</h2>
    </div>
<form method="post" class="login_box" action="register_action.php">
    <h2>Register</h2>
     <div class="buttons">
        <div class="input-group">
    <label>Email:</label><input type="email" name="email" required></label><br>
    <label>Phone:</label> <input type="text" name="phone" required></label><br>
    <label>Password:</label> <input type="password" name="password" required></label><br>
    <label>Confirm Password:</label><input type="password" name="password2" required></label><br>
    <button type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>   
    </div>
</form>
    
=======
<body>
<h2>Create an Account</h2>
<form method="post" action="register_action.php">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Phone: <input type="text" name="phone" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Confirm Password: <input type="password" name="password2" required></label><br>
    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
</body>
</html>
