<?php 
session_start();
require 'db_connect.php';
$error_message = $_SESSION['reg_error'] ?? '';
$success_message = $_SESSION['reg_success'] ?? '';
unset($_SESSION['reg_error'], $_SESSION['reg_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Attendify</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: url("background.jpg") no-repeat center center fixed;
  background-size: cover;
  color: #111;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
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

/* Header */
.Track {
  position: absolute; top: 0; left: 0; width: 100%;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(90deg,#1d2b57,#233b76);
  height: 65px; color: white;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
}
.Track img { position: absolute; left: 10px; height: 65px; }
.Track h2 { font-size: 22px; font-weight: 700; }

/* Register Box */
.login_box {
  background: #fff;
  width: 500px;
  padding: 35px 40px;
  border-radius: 14px;
  box-shadow: 0 10px 35px rgba(0,0,0,0.25);
  animation: fadeIn 0.5s ease;
}
@keyframes fadeIn { from {opacity:0;transform:translateY(-15px);} to {opacity:1;transform:translateY(0);} }
.login_box h2 {
  text-align: center; color: #0E027E; margin-bottom: 25px;
  font-size: 26px; font-weight: bold;
}

/* Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px 20px;
}
.form-grid .full-width { grid-column: span 2; }

label { font-weight: bold; margin-bottom: 5px; color: #1b1b1b; display: block; }
input, select {
  width: 100%; padding: 10px; border: 1px solid #ccc;
  border-radius: 8px; font-size: 14px;
  transition: all 0.3s ease;
}
input:focus, select:focus { border-color: #233b76; box-shadow: 0 0 8px rgba(35,59,118,0.3); outline: none; }
input.error { border-color: #e21b23; background-color: #ffe7e7; }
input.valid { border-color: #1b9c1b; background-color: #f2fff2; }

/* Password Rules */
.password-rules { list-style: none; margin: 5px 0 10px; grid-column: span 2; }
.password-rules li { font-size: 13px; color: #555; }
.password-rules li.valid { color: #0b7a0b; font-weight: 600; }

.password-rules {
  list-style: none;
  margin: 8px 0 12px;
  grid-column: span 2;
  padding-left: 0;
}

.password-rules li {
  font-size: 13px;
  color: #555;
  margin-bottom: 4px;
  transition: color 0.3s ease, transform 0.2s ease;
}

.password-rules li.valid {
  color: #0b7a0b;
  font-weight: 600;
  transform: translateX(4px);
}

.password-rules li.invalid {
  color: #b30000;
  font-weight: normal;
}

.password-rules li::before {
  content: "❌ ";
  color: #b30000;
}

.password-rules li.valid::before {
  content: "✔ ";
  color: #0b7a0b;
}

button {
  grid-column: span 2;
  padding: 12px; border: none; border-radius: 8px;
  font-size: 15px; font-weight: bold;
  background: linear-gradient(90deg, #c0c0c0, #9a9a9a);
  color: white; cursor: not-allowed;
  transition: 0.3s;
}
button.enabled { background: linear-gradient(90deg, #2a45a8, #1e2f82); cursor: pointer; }
button.enabled:hover { transform: scale(1.02); }

/* Alerts */
.alert {
  grid-column: span 2;
  padding: 12px; border-radius: 6px; font-size: 14px;
  animation: popIn 0.4s ease;
}
.alert.error { background: #ffe7e7; border-left: 6px solid #e21b23; color: #800; }
.alert.success { background: #e7f9e7; border-left: 6px solid #13b013; color: #064e06; }
@keyframes popIn { from {opacity:0;transform:translateY(-10px);} to {opacity:1;transform:translateY(0);} }

p { text-align: center; grid-column: span 2; margin-top: 8px; font-size: 13px; }
p a { color: #0E027E; font-weight: bold; text-decoration: none; }
p a:hover { text-decoration: underline; }

@media (max-width: 550px) {
  .login_box { width: 90%; padding: 30px; }
  .form-grid { grid-template-columns: 1fr; }
}
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

    <!-- Alerts -->
    <?php if ($error_message): ?>
      <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif ($success_message): ?>
      <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="full-width">
      <label>Email:</label>
      <input type="email" name="email" id="email" required>
    </div>

    <div>
      <label>Student Phone:</label>
      <input type="text" name="student_phone" id="student_phone" placeholder="+639XXXXXXXXX" pattern="^(?:\+63|0)9\d{9}$" required>
    </div>

    <div>
      <label>Parent/Guardian Phone:</label>
      <input type="text" name="parent_phone" id="parent_phone" placeholder="+639XXXXXXXXX" pattern="^(?:\+63|0)9\d{9}$" required>
    </div>

    <div>
      <label>Password:</label>
      <input type="password" name="password" id="passwordField" required>
    </div>

    <div>
      <label>Confirm Password:</label>
      <input type="password" name="password2" id="confirmPassword" required>
    </div>

    <ul class="password-rules" id="rulesList">
    <li id="rule-special"> Must contain a Special Character (@!#$%^&*)</li>
    <li id="rule-case">Must include Lowercase & Uppercase Letters</li>
    <li id="rule-length"> Must be at least 8 Characters</li>
    <li id="rule-number"> Must include at least one Number (0–9)</li>
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
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</form>
<script>
const password = document.getElementById('passwordField');
const confirm = document.getElementById('confirmPassword');
const email = document.getElementById('email');
const studentPhone = document.getElementById('student_phone');
const parentPhone = document.getElementById('parent_phone');
const role = document.querySelector('select[name="role"]');
const button = document.getElementById('registerBtn');

// Password rule elements
const ruleSpecial = document.getElementById('rule-special');
const ruleCase = document.getElementById('rule-case');
const ruleLength = document.getElementById('rule-length');
const ruleNumber = document.getElementById('rule-number');

function updatePasswordRules() {
  const val = password.value;
  const hasSpecial = /[!@#$%^&*]/.test(val);
  const hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
  const hasLength = val.length >= 8;
  const hasNumber = /\d/.test(val);

  // Update each rule dynamically
  updateRule(ruleSpecial, hasSpecial);
  updateRule(ruleCase, hasCase);
  updateRule(ruleLength, hasLength);
  updateRule(ruleNumber, hasNumber);
}

function updateRule(ruleElement, condition) {
  if (condition) {
    ruleElement.classList.add('valid');
    ruleElement.classList.remove('invalid');
  } else {
    ruleElement.classList.add('invalid');
    ruleElement.classList.remove('valid');
  }
}

function validateForm() {
  const val = password.value.trim();
  const confirmVal = confirm.value.trim();
  const emailVal = email.value.trim();
  const studentVal = studentPhone.value.trim();
  const parentVal = parentPhone.value.trim();
  const roleVal = role.value;

  const phonePattern = /^(?:\+63|0)9\d{9}$/;
  const validStudent = phonePattern.test(studentVal);
  const validParent = phonePattern.test(parentVal);
  const diffPhones = studentVal !== parentVal;
  const validRole = roleVal !== "";
  const strongPass = document.querySelectorAll('.password-rules li.valid').length === 4;
  const passwordsMatch = val && confirmVal && val === confirmVal;
  const allValid = emailVal && validStudent && validParent && diffPhones && validRole && strongPass && passwordsMatch;

  button.disabled = !allValid;
  button.classList.toggle('enabled', allValid);
}

[password, confirm, email, studentPhone, parentPhone, role].forEach(input => {
  input.addEventListener('input', () => {
    updatePasswordRules();
    validateForm();
  });
});
</script>

</body>
</html>
