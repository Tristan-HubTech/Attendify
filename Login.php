<?php require 'db_connect.php'; 
 if (isset($_GET['error'])): ?>
    <p style="color:red; text-align:center;">
        <?php 
            if ($_GET['error'] === 'empty_fields') echo "Please fill in both fields.";
            if ($_GET['error'] === 'invalid_credentials') echo "Invalid email or password.";
        ?>
    </p>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<link rel="stylesheet" href="login.css">
<body>
    <div class="Track">
        <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
        <h2>Welcome to Attendify</h2>
    </div>
<form method="post" class="login_box" action="login_action.php">
    <h2>Login</h2>
    <div class="buttons">
        <div class="input-group">
    <label>Email:</label><input type="email" name="email" required></label><br>
    <label>Password:</label><input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
    <p><a href="request_reset.php">Forgot password?</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>  
        </div>   
    </div>
</form>
<script>
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
</script>


</body>
</html>
