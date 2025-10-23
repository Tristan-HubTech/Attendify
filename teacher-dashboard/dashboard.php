<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['teacher'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .dashboard {
            width: 80%;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .logout-btn {
            background-color: #e63946;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            float: right;
            margin-top: -40px;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background-color: #d62828;
        }
        .btn {
            background-color: #457b9d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #1d3557;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['teacher']); ?>!</h1>
    <a href="logout.php" class="logout-btn">Logout</a>

    <h2>📊 Attendance Management</h2>
    <p>Click below to open your attendance sheet.</p>
    <a href="attendance.php" class="btn">Open Attendance Sheet</a>
</div>
</body>
</html>
