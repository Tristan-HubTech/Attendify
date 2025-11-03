<?php
session_start();
require '../db_connect.php';

// Make sure only teachers can add subjects
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');

    if ($subject_name === '') {
        $message = "Please enter a subject name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (name, teacher_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("si", $subject_name, $teacher_id);
            if ($stmt->execute()) {
                $message = "✅ Subject added successfully.";
            } else {
                $message = "❌ Failed to add subject: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Subject</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    margin: 0;
}
.container {
    max-width: 500px;
    margin: 100px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h2 { text-align: center; color: #333; }
form { display: flex; flex-direction: column; gap: 10px; }
input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    padding: 10px;
    background: #222;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover { background: #444; }
.message {
    text-align: center;
    margin-bottom: 10px;
    color: #007b00;
}
a {
    text-decoration: none;
    color: #222;
    display: block;
    text-align: center;
    margin-top: 10px;
}
</style>
</head>
<body>
<div class="container">
    <h2>Add Subject</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="subject_name" placeholder="Enter subject name" required>
        <button type="submit">Add Subject</button>
    </form>
    <a href="attendance.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>
