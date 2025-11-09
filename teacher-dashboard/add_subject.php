<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

/* ================================
   ✅ Log page view
================================ */
log_activity($conn, $teacher_id, 'teacher', 'View Add Subject Page', 'Teacher accessed the Add Subject page.');

/* ================================
   ✅ Handle Form Submission
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');

    if ($subject_name === '') {
        $message = "⚠️ Please enter a subject name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (name, teacher_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("si", $subject_name, $teacher_id);
            if ($stmt->execute()) {
                $message = "✅ Subject added successfully.";

                // ✅ Log successful subject creation
                log_activity($conn, $teacher_id, 'teacher', 'Add Subject', 'Added new subject: ' . $subject_name);
            } else {
                $message = "❌ Failed to add subject: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Subject | Teacher Panel</title>
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
h2 { text-align: center; color: #17345f; }
form { display: flex; flex-direction: column; gap: 10px; }
input[type="text"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    padding: 10px;
    background: #17345f;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover { background: #e21b23; }
.message {
    text-align: center;
    margin-bottom: 10px;
    color: #007b00;
    font-weight: bold;
}
a {
    text-decoration: none;
    color: #17345f;
    display: block;
    text-align: center;
    margin-top: 10px;
}
a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
    <h2>📘 Add New Subject</h2>
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
