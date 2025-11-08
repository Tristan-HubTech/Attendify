<?php
include('db_connect.php');
session_start();

// ✅ Redirect users based on their role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin-dashboard/admin.php");
        exit();
    } elseif ($_SESSION['role'] === 'teacher') {
        header("Location: teacher-dashboard/attendance.php");
        exit();
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: students-dashboard/index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendify | ACLC College of Mandaue</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: url('background.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      color: white;
      position: relative;
      padding-top: 90px;
      overflow: hidden;
    }

    /* BLUR OVERLAY */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(8px) brightness(0.6);
      z-index: -1;
    }

    /* HEADER BAR */
    .main-header {
      background-color: rgba(23, 52, 95, 0.85); /* semi-transparent blue */
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      padding: 12px 0;
      backdrop-filter: blur(6px);
    }

    /* FLEX CONTAINER */
    .header-content {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 25px;
    }

    /* LEFT LOGO (Flush to edge) */
    .logo-box {
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }

    .aclc-logo {
      height: 60px;
      width: auto;
    }

    /* CENTER TITLE */
    .center-title {
      text-align: center;
      flex-grow: 1;
    }

    .main-title {
      font-size: 30px;
      font-weight: bold;
      color: white;
      letter-spacing: 1px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
    }

    /* RIGHT BUTTONS (Flush to edge) */
    .button-box {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 12px;
    }

    .log, .reg {
      color: white;
      text-decoration: none;
      font-size: 15px;
      padding: 8px 18px;
      border: 1px solid white;
      border-radius: 5px;
      transition: all 0.3s ease;
      backdrop-filter: blur(4px);
    }

    .log:hover, .reg:hover {
      background-color: #e21b23;
      border-color: #e21b23;
    }

    /* HERO SECTION */
    .hero {
      text-align: center;
      margin-top: 120px;
      padding: 40px 20px;
      color: white;
    }

    .hero h1 {
      font-size: 45px;
      font-weight: bold;
      margin-bottom: 15px;
      text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
    }

    .hero p {
      font-size: 18px;
      opacity: 0.9;
      margin-bottom: 25px;
    }

    .hero a {
      display: inline-block;
      text-decoration: none;
      color: white;
      background: #e21b23;
      padding: 12px 28px;
      border-radius: 5px;
      font-weight: bold;
      font-size: 16px;
      transition: 0.3s ease;
      border: 2px solid transparent;
    }

    .hero a:hover {
      background: transparent;
      border: 2px solid #e21b23;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }

      .main-title {
        font-size: 24px;
      }

      .aclc-logo {
        height: 50px;
      }

      .button-box {
        justify-content: center;
      }

      .hero h1 {
        font-size: 34px;
      }
    }
  </style>
</head>
<body>
  <header class="main-header">
    <div class="header-content">

      <!-- Left: ACLC Logo (flush left) -->
      <div class="logo-box">
        <img src="ama.png" alt="ACLC Logo" class="aclc-logo">
      </div>

      <!-- Center: Title -->
      <div class="center-title">
        <h1 class="main-title">Attendify</h1>
      </div>

      <!-- Right: Buttons (flush right) -->
      <div class="button-box">
        <a href="register.php" class="reg">Register</a>
        <a href="login.php" class="log">Login</a>
      </div>

    </div>
  </header>

  <!-- HERO SECTION -->
  <section class="hero">
    
  </section>
</body>
</html>
