<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="Track">
        <img src="ama.png" class="aclc-logo" alt="ACLC Logo">
        <h2>Welcome to Attendify</h2>
    </div>

    <form method="post" class="login_box" action="register_action.php">
        <h2>Register</h2>
        <div class="buttons">
            <div class="input-group">
                <label>Email:</label>
                <input type="email" name="email" required><br>

                <label>Student Phone:</label>
                <input type="text" name="student_phone" placeholder="(+63)xxx xxx xxxx" required><br><br>

                <label>Parent/Guardian Phone:</label>
                <input type="text" name="parent_phone" placeholder="(+63)xxx xxx xxxx" required><br><br>

                <label>Password:</label>
                <input type="password" name="password" required><br>

                <label>Confirm Password:</label>
                <input type="password" name="password2" required><br>

                <label>Role:</label>
                <select name="role" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select><br><br>

                <button type="submit">Register</button>

                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </form>
</body>
</html>