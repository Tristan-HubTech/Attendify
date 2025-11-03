<?php
session_start();
require '../db_connect.php';

// Only students allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Check if student already filled out info
$check = $conn->prepare("SELECT id FROM students WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $birthday = $_POST['birthday'];
    $student_id = trim($_POST['student_id']);
    $section = trim($_POST['section']);

    if ($name && $address && $birthday && $student_id && $section) {
        $stmt = $conn->prepare("INSERT INTO students (name, email, address, birthday, student_id, section) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $address, $birthday, $student_id, $section);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Profile saved successfully.'); window.location.href='index.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Profile Setup</title>
<style>
body {font-family: Arial, sans-serif; background:#f8f9fa; margin:0;}
.container {width:60%; margin:40px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
input, select {width:100%; padding:8px; margin:8px 0;}
button {background:#1d3557; color:white; border:none; padding:10px 15px; border-radius:5px; cursor:pointer;}
button:hover {background:#457b9d;}
</style>
</head>
<body>
<div class="container">
<h2>Fill Out Your Student Information</h2>
<form method="POST">
    <label>Full Name:</label>
    <input type="text" name="name" required>

    <label>Address:</label>
    <input type="text" name="address" required>

    <label>Birthday:</label>
    <input type="date" name="birthday" required>

    <label>Student ID:</label>
    <input type="text" name="student_id" required>

    <label>Section:</label>
    <select name="section" required>
        <?php
        $secRes = $conn->query("SELECT name FROM sections");
        if ($secRes->num_rows > 0) {
            while ($row = $secRes->fetch_assoc()) {
                echo "<option value='".htmlspecialchars($row['name'])."'>".htmlspecialchars($row['name'])."</option>";
            }
        } else {
            echo "<option value=''>No sections available</option>";
        }
        ?>
    </select>

    <button type="submit">Save Profile</button>
</form>
</div>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 35c24ba (initail commit)
