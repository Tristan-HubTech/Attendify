<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Attendify</title>
    <link rel="stylesheet" href="register_new.css">
</head>
<body>
    <!-- HEADER -->
    <div class="Track">
        <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
        <h2>Welcome to Attendify</h2>
    </div>

    <!-- REGISTER FORM -->
    <form method="post" class="login_box" action="register_action.php">
        <h2>Register</h2>

        <div class="input-group">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Student Phone:</label>
            <input type="text" name="student_phone" placeholder="(+63)xxx xxx xxxx" required>

            <label>Parent/Guardian Phone:</label>
            <input type="text" name="parent_phone" placeholder="(+63)xxx xxx xxxx" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Confirm Password:</label>
            <input type="password" name="password2" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>

            <button type="submit">Register</button>

            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>

   
</body>
</html>
