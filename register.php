<?php
session_start();
require 'db_connect.php';
$error_message = $_SESSION['reg_error'] ?? '';
unset($_SESSION['reg_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Attendify</title>
<style>
/* === GLOBAL RESET === */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: url("background.jpg") no-repeat center center fixed;
  background-size: cover;
  color: #111;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh; /* fills screen perfectly */
  overflow: hidden;
  position: relative;
}
body::before {
  content: "";
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(6px);
  z-index: -1;
}

/* === HEADER === */
.Track {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(90deg,#1d2b57,#233b76);
  height: 65px;
  color: white;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
}
.Track img { position: absolute; left: 10px; height: 65px; }
.Track h2 { font-size: 22px; font-weight: 700; }

/* === REGISTER BOX === */
.login_box {
  background: #fff;
  width: 500px;
  padding: 35px 40px;
  border-radius: 14px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 10px 35px rgba(0,0,0,0.25);
  animation: fadeIn 0.5s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.login_box h2 {
  text-align: center;
  color: #0E027E;
  margin-bottom: 25px;
  font-size: 26px;
  font-weight: bold;
}

/* === FORM GRID === */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px 20px;
}
.form-grid .full-width { grid-column: span 2; }

label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
  color: #1b1b1b;
}
input, select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: inset 0 2px 3px rgba(0,0,0,0.05);
}
input:focus, select:focus {
  border-color: #233b76;
  box-shadow: 0 0 8px rgba(35,59,118,0.3);
  outline: none;
}

/* === PASSWORD RULES === */
.password-rules {
  list-style: none;
  margin: 5px 0 10px 5px;
  grid-column: span 2;
  padding-left: 0;
}
.password-rules li {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #555;
  padding: 2px 0;
  transition: color 0.3s, transform 0.3s;
}
.password-rules li::before {
  content: "•";
  color: #888;
  font-weight: bold;
}
.password-rules li.valid {
  color: #0b7a0b;
  font-weight: 600;
}
.password-rules li.valid::before {
  content: "✔";
  color: #0b7a0b;
}

/* === BUTTON === */
button {
  grid-column: span 2;
  padding: 12px;
  background: linear-gradient(90deg, #c0c0c0, #9a9a9a);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: bold;
  cursor: not-allowed;
  transition: all 0.3s ease;
}
button.enabled {
  background: linear-gradient(90deg, #2a45a8, #1e2f82);
  cursor: pointer;
}
button.enabled:hover {
  background: linear-gradient(90deg, #1c2a6d, #151d73);
  transform: scale(1.02);
}

/* === WARNING BOX === */
.register-warning {
  grid-column: span 2;
  background: #fff2f2;
  border-left: 5px solid #e21b23;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 13px;
  color: #a80000;
  margin-top: 8px;
  animation: fadeWarn 0.5s ease forwards;
}
@keyframes fadeWarn { to { opacity: 1; } }

/* === FOOTER LINK === */
p {
  text-align: center;
  grid-column: span 2;
  margin-top: 8px;
  font-size: 13px;
}
p a {
  color: #0E027E;
  font-weight: bold;
  text-decoration: none;
}
p a:hover {
  color: #233b76;
  text-decoration: underline;
}

/* === RESPONSIVE === */
@media (max-width: 550px) {
  .login_box { width: 90%; padding: 30px; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>

</style>
</head>
<body>

<div class="Track">
  <img src="ama.png" alt="ACLC Logo">
  <h2>Welcome to Attendify</h2>
</div>

<form method="POST" class="login_box" action="register_action.php" autocomplete="off">
  <h2>Register</h2>
  <div class="form-grid">
    <div class="full-width">
      <label>Email:</label>
      <input type="email" name="email" required>
    </div>

    <div>
      <label>Student Phone:</label>
      <input type="text" name="student_phone" placeholder="+639XXXXXXXXX" pattern="^(?:\+63|0)9\d{9}$" required>
    </div>

    <div>
      <label>Parent/Guardian Phone:</label>
      <input type="text" name="parent_phone" placeholder="+639XXXXXXXXX" pattern="^(?:\+63|0)9\d{9}$" required>
    </div>

    <div>
      <label>Password:</label>
      <input type="password" name="password" required id="passwordField">
    </div>

    <div>
      <label>Confirm Password:</label>
      <input type="password" name="password2" required id="confirmPassword">
    </div>

    <ul class="password-rules" id="rulesList">
      <li>Special Character (@!#$%^&*)</li>
      <li>Lowercase & Uppercase Letters</li>
      <li>At least 8 Characters</li>
      <li>Include at least one Number (0–9)</li>
    </ul>

    <div class="full-width">
      <label>Role:</label>
      <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <button type="submit" id="registerBtn">Register</button>

    <div class="register-warning" id="warningBox">
      ⚠️ Please ensure your password meets the security policy to protect your account.
    </div>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</form>

<script>
const warn = document.getElementById('warningBox');
setTimeout(()=> warn.remove(), 5000);

const password = document.getElementById('passwordField');
const confirm = document.getElementById('confirmPassword');
const rules = document.querySelectorAll('#rulesList li');
const button = document.getElementById('registerBtn');

function validatePassword() {
  const val = password.value;
  const checks = [
    /[!@#$%^&*]/.test(val),
    /[a-z]/.test(val) && /[A-Z]/.test(val),
    val.length >= 8,
    /\d/.test(val)
  ];

  // update UI
  rules.forEach((li, i) => li.classList.toggle('valid', checks[i]));

  // enable/disable button
  if (checks.every(Boolean)) {
    button.classList.add('enabled');
    button.disabled = false;
  } else {
    button.classList.remove('enabled');
    button.disabled = true;
  }
}

password.addEventListener('input', validatePassword);

document.querySelector('form').addEventListener('submit', e => {
  if (password.value !== confirm.value) {
    e.preventDefault();
    showToast("Those passwords didn’t match. Try again.");
  }
});

function showToast(msg) {
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(()=> toast.remove(), 3500);
}
</script>
</body>
</html>
