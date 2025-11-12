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
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: url('background.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      color: white;
      position: relative;
      overflow-x: hidden;
    }

    /* BLUR OVERLAY */
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      backdrop-filter: blur(8px) brightness(0.6);
      z-index: -1;
    }

    /* HEADER */
    .main-header {
      background-color: rgba(23, 52, 95, 0.9);
      width: 100%;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 40px;
    }

    .logo-box {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-box img {
      height: 55px;
      width: auto;
    }

    .main-title {
      flex-grow: 1;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      color: white;
      letter-spacing: 1px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
    }

    .button-box {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .log, .reg {
      color: white;
      text-decoration: none;
      font-size: 15px;
      padding: 8px 18px;
      border: 1px solid white;
      border-radius: 5px;
      transition: 0.3s;
    }

    .log:hover, .reg:hover {
      background-color: #e21b23;
      border-color: #e21b23;
    }

    /* HERO */
    .hero {
      text-align: center;
      margin-top: 150px;
      padding: 30px 20px 50px;
      animation: fadeIn 1s ease;
    }

    .hero h1 {
      font-size: 50px;
      font-weight: bold;
      margin-bottom: 10px;
      text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
    }

    .hero h3 {
      font-size: 22px;
      color: #ffd700;
      letter-spacing: 1px;
      margin-bottom: 25px;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
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

    /* FEATURES */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      width: 85%;
      max-width: 1100px;
      margin: 40px auto 90px;
      animation: fadeInUp 1.2s ease;
    }

    .feature {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 25px 20px;
      transition: 0.3s;
      box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }

    .feature:hover {
      transform: translateY(-6px);
      background: rgba(255, 255, 255, 0.25);
    }

    .feature h3 {
      color: #ffd700;
      font-size: 18px;
      margin-bottom: 8px;
    }

    .feature p {
      color: #eaeaea;
      font-size: 14px;
      line-height: 1.5;
    }

    /* FOOTER */
    footer {
      text-align: center;
      padding: 12px 10px;
      color: #ddd;
      font-size: 13px;
      background: rgba(0, 0, 0, 0.45);
      backdrop-filter: blur(8px);
      border-top: 1px solid rgba(255,255,255,0.1);
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .main-title {
        position: static;
        transform: none;
        text-align: center;
        font-size: 24px;
      }

      .hero h1 { font-size: 36px; }
      .hero h3 { font-size: 18px; }
      .feature { text-align: center; }
    }
  </style>
</head>
<body>
  <header class="main-header">
    <div class="logo-box"><img src="ama.png" alt="ACLC Logo"></div>
    <h1 class="main-title">Attendify</h1>
    <div class="button-box">
      <a href="register.php" class="reg">Register</a>
      <a href="login.php" class="log">Login</a>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section class="hero">
    <h1>Welcome to Attendify</h1>
    <h3>Simplify. Track. Connect.</h3>
    <a href="login.php">Get Started</a>
  </section>

  <!-- FEATURE SECTION -->
  <div class="features">
    <div class="feature">
      <h3>🕒 Effortless Attendance</h3>
      <p>Log attendance via QR codes or manually — fast, easy, and reliable for every class session.</p>
    </div>
    <div class="feature">
      <h3>📱 Instant Updates</h3>
      <p>Receive real-time SMS alerts and view attendance reports instantly with detailed analytics.</p>
    </div>
    <div class="feature">
      <h3>🔐 Dedicated Access Panels</h3>
      <p>Separate dashboards for Students, Parents, Teachers, and Administrators to keep everyone informed.</p>
    </div>
    <div class="feature">
      <h3>📊 Boost Accountability</h3>
      <p>Track performance and attendance patterns to build stronger student-parent-teacher engagement.</p>
    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    Attendify v1.0 | © 2025 ACLC College of Mandaue — Developed by <strong>BSIT Attendify Members</strong>
  </footer>
</body>
</html>
