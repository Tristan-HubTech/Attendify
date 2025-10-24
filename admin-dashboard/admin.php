<?php
session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .dashboard h1 {
            margin-top: 0;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background: #d9534f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background: #c9302c;
        }
        .content {
            margin-top: 20px;
        }
        .card-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: #e7f1ff;
            padding: 20px;
            flex: 1;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo $_SESSION['admin']; ?>!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>

        <div class="content">
            <p>This is the admin dashboard.</p>
            <p>You can add your features here (attendance, subjects, students, reports, etc.).</p>

            <div class="card-container">
                <div class="card">
                    <h3>Total Students</h3>
                    <p>25</p>
                </div>
                <div class="card">
                    <h3>Subjects</h3>
                    <p>8</p>
                </div>
                <div class="card">
                    <h3>Attendance Today</h3>
                    <p>85%</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
