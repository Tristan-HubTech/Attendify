<?php
session_start();
require 'db_connect.php';

// Handle login errors
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') $error_message = "⚠️ Please fill in both fields.";
    if ($_GET['error'] === 'invalid_credentials') $error_message = "❌ Invalid email or password.";
}
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Attendify</title>
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

/* HEADER BAR */
.Track {
  width: 100%;
  display: flex;
  background: linear-gradient(90deg, #1d2b57, #233b76);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
  height: 70px;
}

.Track h2 {
  margin: auto;
  font-size: 24px;
  color: white;
  text-align: center;
  margin-right: auto;
  margin-left: -60px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
}

.aclc-logo {
  height: 70px;
  width: auto;
  margin-right: auto;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.4));
}

/* BACKGROUND */
body::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url("background.jpg") no-repeat center center fixed;
  background-size: cover;
  filter: blur(6px) brightness(0.85);
  z-index: -2;
}

body::after {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.45);
  z-index: -1;
}

/* LOGIN BOX */
.login_box {
  background-color: #ffffff;
  padding: 60px;
  border-radius: 12px;
  width: 450px;
  margin: 140px auto;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  color: black;
  transition: all 0.3s ease;
}
.login_box:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.login_box h2 {
  text-align: center;
  margin-bottom: 40px;
  color: #0E027E;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

/* INPUT GROUPS */
.input-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

.input-group label {
  font-weight: bold;
  margin-bottom: 6px;
  color: #1d1d1d;
}

.input-group input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: 0.3s;
  box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
}

.input-group input:focus {
  border-color: #233b76;
  outline: none;
  box-shadow: 0 0 8px rgba(35, 59, 118, 0.5);
}

/* BUTTON */
button {
  width: 100%;
  padding: 12px;
  background: linear-gradient(90deg, #233b76, #0E027E);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: 0.3s;
  box-shadow: 0 4px 12px rgba(35, 59, 118, 0.5);
  position: relative;
  overflow: hidden;
}

button:hover {
  background: linear-gradient(90deg, #1c2a6d, #09016f);
  transform: scale(1.02);
  box-shadow: 0 6px 15px rgba(0,0,0,0.4);
}

/* RIPPLE EFFECT */
button .ripple {
  position: absolute;
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 50%;
  transform: scale(0);
  animation: rippleEffect 0.6s linear;
  pointer-events: none;
}
@keyframes rippleEffect {
  to { transform: scale(4); opacity: 0; }
}

/* ERROR BOX */
.error-box {
  background: #ffd2d2;
  border-left: 5px solid #d8000c;
  color: #a80000;
  padding: 10px;
  font-size: 14px;
  border-radius: 6px;
  margin-bottom: 15px;
  text-align: center;
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* CAPS WARNING */
.caps-warning {
  color: #c70000;
  font-size: 12px;
  margin-top: 5px;
  display: none;
}

/* LINKS */
p {
  text-align: center;
  font-size: 14px;
  margin-top: 10px;
}

p a {
  color: #0E027E;
  text-decoration: none;
  font-weight: bold;
}

p a:hover {
  text-decoration: underline;
}

/* RESPONSIVE */
@media (max-width: 600px) {
  .login_box {
    width: 85%;
    padding: 40px;
  }
  .Track h2 {
    font-size: 20px;
  }
  .aclc-logo {
    height: 50px;
  }
}
</style>
</head>
<body>

<div class="Track">
  <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
  <h2>Welcome to Attendify</h2>
</div>

<form method="POST" class="login_box" action="login_action.php" autocomplete="off">
  <h2>Login</h2>

  <?php if ($error_message): ?>
  <div class="error-box"><?= htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <div class="input-group">
    <label>Email:</label>
    <input type="email" name="email" placeholder="Enter your email" required>
  </div>

  <div class="input-group">
    <label>Password:</label>
    <input type="password" name="password" placeholder="Enter your password" required id="passwordField">
    <small id="capsWarning" class="caps-warning">⚠️ Caps Lock is ON</small>
  </div>

  <button type="submit">Login</button>

  <p><a href="request_reset.php">Forgot password?</a></p>
  <p>Don't have an account? <a href="register.php">Register here</a></p>
</form>

<script>
// Ripple animation
document.querySelectorAll('button').forEach(btn => {
  btn.addEventListener('click', e => {
    const ripple = document.createElement('span');
    ripple.classList.add('ripple');
    btn.appendChild(ripple);

    const x = e.clientX - e.target.offsetLeft;
    const y = e.clientY - e.target.offsetTop;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;

    setTimeout(() => ripple.remove(), 600);
  });
});

// Caps Lock detection
const passwordInput = document.getElementById('passwordField');
const capsWarning = document.getElementById('capsWarning');
passwordInput.addEventListener('keyup', (e) => {
  const caps = e.getModifierState('CapsLock');
  capsWarning.style.display = caps ? 'block' : 'none';
});
</script>

</body>
</html>
