<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<link rel="stylesheet" href="verify_otp.css">
<body>
     <div class="Track">
    <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
    <h2>Welcome to Attendify</h2>
    </div>
<form method="post" class="login_box" action="verify_otp_action.php">
    <h2>Enter OTP</h2>
    <div class="buttons">
        <div class="input-group">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES); ?>">
    <label>OTP:</label><input type="text" name="otp" maxlength="6" required></label><br>
    <button type="submit">Verify</button>
     </div>   
    </div>
</form>
</body>
</html>
