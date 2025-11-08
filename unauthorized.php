<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Access Denied | Attendify</title>
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
    .error-box {
      background-color: white;
      padding: 60px;
      border-radius: 12px;
      width: 500px;
      margin: 160px auto;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
      color: black;
      text-align: center;
      animation: fadeIn 0.6s ease;
    }
    .error-box h1 {
      color: #e21b23;
      font-size: 28px;
      margin-bottom: 15px;
    }
    .error-box p {
      color: #333;
      font-size: 16px;
      margin-bottom: 25px;
    }
    .error-box a {
      display: inline-block;
      text-decoration: none;
      background-color: #0E027E;
      color: white;
      padding: 12px 25px;
      border-radius: 6px;
      font-weight: bold;
      transition: 0.3s;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
    }
    .error-box a:hover {
      background-color: #09016f;
      transform: scale(1.03);
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

  <div class="error-box">
    <h1>Access Denied</h1>
    <p>Your session has expired or this page is restricted.<br>
    Please log in again to continue using Attendify.</p>
    <a href="login.php">Return to Login</a>
  </div>
</body>
</html>
