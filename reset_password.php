<?php 
require 'db_connect.php';
session_start();

$allowed = isset($_SESSION['password_reset_allowed']) && $_SESSION['password_reset_allowed'] >= time();
if (!isset($_SESSION['password_reset_user']) || !$allowed) {
    header("Location: unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password | Attendify</title>
<link rel="stylesheet" href="reset_password.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: Arial, sans-serif;
  color: white;
  height: 100vh;
  overflow: hidden;
  position: relative;
}
.Track {
  width: 100%;
  display: flex;
  background: linear-gradient(90deg, #1d2b57, #233b76);
  height: 70px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
}
.Track h2 {
  margin: auto;
  font-size: 24px;
  color: white;
  text-align: center;
  margin-right: auto;
  margin-left: -60px;
}
.aclc-logo { height: 70px; margin-right: auto; }

body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: url("background.jpg") no-repeat center center fixed;
  background-size: cover;
  filter: blur(6px) brightness(0.85);
  z-index: -2;
}
body::after {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.45);
  z-index: -1;
}

/* FORM */
.login_box {
  background-color: #fff;
  padding: 60px;
  border-radius: 12px;
  width: 500px;
  margin: 120px auto;
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  color: black;
  animation: fadeIn 0.6s ease;
}
.login_box h2 {
  text-align: center;
  margin-bottom: 40px;
  color: #0E027E;
}
.input-group {
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
}
.input-group label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #1d1d1d;
}
.input-group input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: 0.3s;
}
.input-group input:focus {
  border-color: #233b76;
  box-shadow: 0 0 6px rgba(35,59,118,0.5);
}
.error-text {
  color: #d8000c;
  font-size: 13px;
  margin-top: 4px;
  display: none;
}

/* Password rules */
.password-rules {
  font-size: 14px;
  color: #444;
  margin: 10px 0;
}
.password-rules li {
  list-style: none;
  margin: 4px 0;
}
.password-rules li.valid { color: green; }
.password-rules li.invalid { color: red; }

/* Button */
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
}
button:hover {
  background: linear-gradient(90deg, #1c2a6d, #09016f);
  transform: scale(1.02);
}

@keyframes fadeIn { from{opacity:0;transform:translateY(-15px);} to{opacity:1;transform:translateY(0);} }
</style>
</head>
<body>
<div class="Track">
  <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
  <h2>Welcome to Attendify</h2>
</div>

<form id="resetForm" class="login_box" method="POST" action="reset_password_action.php" autocomplete="off">
  <h2>Set a New Password</h2>

  <div class="input-group">
    <label for="password">New Password:</label>
    <input type="password" id="password" name="password" required>
    <div id="passwordError" class="error-text">Password must meet all requirements.</div>
  </div>

  <div class="input-group">
    <label for="password2">Confirm Password:</label>
    <input type="password" id="password2" name="password2" required>
    <div id="matchError" class="error-text">Passwords do not match.</div>
  </div>

  <ul class="password-rules" id="passwordRules">
    <li id="ruleLength" class="invalid">✗ At least 8 characters</li>
    <li id="ruleUpper" class="invalid">✗ Uppercase & Lowercase letters</li>
    <li id="ruleNumber" class="invalid">✗ At least one number (0–9)</li>
    <li id="ruleSpecial" class="invalid">✗ At least one special character (@!#$%^&*)</li>
  </ul>

  <button type="submit">Save New Password</button>
</form>

<script>
const password = document.getElementById("password");
const password2 = document.getElementById("password2");
const passwordError = document.getElementById("passwordError");
const matchError = document.getElementById("matchError");
const rules = {
  length: document.getElementById("ruleLength"),
  upper: document.getElementById("ruleUpper"),
  number: document.getElementById("ruleNumber"),
  special: document.getElementById("ruleSpecial")
};

password.addEventListener("input", () => {
  const val = password.value;
  const length = val.length >= 8;
  const upper = /[A-Z]/.test(val) && /[a-z]/.test(val);
  const number = /\d/.test(val);
  const special = /[!@#$%^&*]/.test(val);

  // Update rules visually
  updateRule(rules.length, length, "At least 8 characters");
  updateRule(rules.upper, upper, "Uppercase & Lowercase letters");
  updateRule(rules.number, number, "At least one number (0–9)");
  updateRule(rules.special, special, "At least one special character (@!#$%^&*)");

  const valid = length && upper && number && special;
  passwordError.style.display = valid ? "none" : "block";
});

function updateRule(el, isValid, text) {
  el.className = isValid ? "valid" : "invalid";
  el.textContent = `${isValid ? "✓" : "✗"} ${text}`;
}

// Form validation
document.getElementById("resetForm").addEventListener("submit", function(e) {
  let valid = true;

  // Check password match
  if (password.value !== password2.value) {
    matchError.style.display = "block";
    valid = false;
  } else {
    matchError.style.display = "none";
  }

  // Check all rules valid
  const allValid = [...document.querySelectorAll(".password-rules li")].every(li => li.classList.contains("valid"));
  if (!allValid) {
    passwordError.style.display = "block";
    valid = false;
  } else {
    passwordError.style.display = "none";
  }

  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
