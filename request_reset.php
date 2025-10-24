<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
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
</form>
</body>
</html>
