<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<<<<<<< HEAD
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
=======
<body>
<h2>Enter OTP</h2>
<form method="post" action="verify_otp_action.php">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES); ?>">
    <label>OTP: <input type="text" name="otp" maxlength="6" required></label><br>
    <button type="submit">Verify</button>
>>>>>>> 1855cf279e6b474bfcad14574796ea93e45d79c6
</form>
</body>
</html>
