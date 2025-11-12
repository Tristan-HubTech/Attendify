<?php
session_start();
$success = $_SESSION['otp_success'] ?? '';
$error = $_SESSION['otp_error'] ?? '';
unset($_SESSION['otp_success'], $_SESSION['otp_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP | Attendify</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      color: white;
      height: 100vh;
      overflow: hidden;
      position: relative;
    }

    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url("background.jpg") no-repeat center center fixed;
      background-size: cover;
      filter: blur(7px) brightness(0.6);
      z-index: -2;
    }

    body::after {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.55);
      z-index: -1;
    }

    .Track {
      width: 100%;
      display: flex;
      background-color: #1b2a5b;
      height: 70px;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.4);
    }

    .Track h2 {
      margin: auto;
      font-size: 24px;
      color: white;
      text-align: center;
      margin-right: auto;
      margin-left: -60px;
      font-weight: 600;
    }

    .aclc-logo {
      height: 70px;
      width: auto;
      margin-right: auto;
    }

    .login_box {
      background-color: #fff;
      padding: 60px;
      border-radius: 12px;
      width: 450px;
      margin: 130px auto;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
      color: black;
      animation: fadeIn 0.6s ease;
    }

    .login_box h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #0E027E;
      font-weight: bold;
    }

    .input-group {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-weight: bold;
      margin-bottom: 6px;
      color: #333;
    }

    .input-group input {
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      transition: 0.3s;
      margin-bottom: 10px;
    }

    .input-group input:focus {
      border-color: #0E027E;
      outline: none;
      box-shadow: 0 0 6px rgba(14, 2, 126, 0.4);
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #0E027E;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.3s;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
    }

    button:hover {
      background-color: #09016f;
      transform: scale(1.02);
    }

    /* ✅ Notification Styles */
    .message-box {
      width: 90%;
      max-width: 420px;
      margin: 20px auto;
      padding: 10px 15px;
      border-radius: 6px;
      font-size: 14px;
      text-align: center;
      font-weight: bold;
      box-shadow: 0 3px 6px rgba(0,0,0,0.3);
      animation: fadeIn 0.6s ease;
    }

    .error-box {
      background: #ffebee;
      color: #a40000;
      border: 1px solid #ff6b6b;
    }

    .success-box {
      background: #e8f9ee;
      color: #056d33;
      border: 1px solid #3dc97d;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="Track">
    <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
    <h2>Welcome to Attendify</h2>
  </div>

  <!-- ✅ Success / Error Messages -->
  <?php if ($success): ?>
    <div class="message-box success-box"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="message-box error-box"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" class="login_box" action="verify_otp_action.php">
    <h2>Enter Your OTP</h2>

    <div class="input-group">
      <label>OTP Code:</label>
      <input type="text" name="otp" maxlength="6" required>
      <button type="submit">Verify OTP</button>
    </div>
  </form>

  <script>
    const msgBox = document.querySelector(".message-box");
    if (msgBox) {
      setTimeout(() => msgBox.style.display = "none", 4000);
    }
  </script>
</body>
</html>
