<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<<<<<<< HEAD
<link rel="stylesheet" href="request_reset.css">
<body>
      <div class="Track">
    <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
    <h2>Welcome to Attendify</h2>
    </div>

    <form method="post" class="login_box" action="send_reset_otp.php">
    <h2>Reset Password</h2>
    <div class="buttons">
        <div class="input-group">
    <label>Email:</label><input type="email" name="email" required></label><br>
    <button type="submit">Send OTP</button>
     </div>   
    </div>
=======
<body>
<h2>Reset Password</h2>
<form method="post" action="send_reset_otp.php">
    <label>Email: <input type="email" name="email" required></label><br>
    <button type="submit">Send OTP</button>
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
</form>
</body>
</html>
