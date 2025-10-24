<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0E027E, #5B5CFF);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .container {
            text-align: center;
            background: rgba(0,0,0,0.4);
            padding: 50px 80px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        p {
            font-size: 20px;
            margin-bottom: 30px;
        }

        a {
            text-decoration: none;
            color: #0E027E;
            background: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        a:hover {
            background: #e0e0ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚧 Dashboard Under Construction 🚧</h1>
        <p>We are working hard to bring you a full experience!</p>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>
