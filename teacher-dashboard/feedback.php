<?php
session_start();
require '../db_connect.php';
require '../log_activity.php'; // ✅ Include activity logging

// ✅ Restrict to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

/* ---------- Log page access ---------- */
log_activity($conn, $teacher_id, 'teacher', 'View Feedback Page', 'Opened the feedback page.');

/* ---------- Handle feedback submission ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $content = trim($_POST['message']);

    if ($subject === '' || $content === '') {
        $message = "⚠️ Please fill out all fields before submitting.";
    } else {
        // ✅ Insert feedback into database (create table if needed)
        $conn->query("
            CREATE TABLE IF NOT EXISTS feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                role VARCHAR(50),
                subject VARCHAR(255),
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $stmt = $conn->prepare("INSERT INTO feedback (user_id, role, subject, message) VALUES (?, 'teacher', ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $teacher_id, $subject, $content);
            if ($stmt->execute()) {
                $message = "✅ Feedback submitted successfully!";
                // ✅ Log to activity
                log_activity($conn, $teacher_id, 'teacher', 'Submit Feedback', "Feedback submitted: $subject");
            } else {
                $message = "❌ Failed to send feedback: " . $stmt->error;
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
<title>Teacher Feedback | Attendify</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
    display: flex;
    height: 100vh;
}

/* SIDEBAR */
.sidebar {
    width: 210px;
    background: #17345f;
    color: white;
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 15px;
}
.sidebar img {
    width: 55%;
    margin-bottom: 10px;
    border-radius: 5px;
}
.sidebar h2 {
    font-size: 16px;
    margin-bottom: 20px;
    text-align: center;
}
.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    width: 85%;
    text-align: left;
    border-radius: 5px;
    margin: 3px 0;
    font-size: 14px;
    transition: 0.3s;
}
.sidebar a:hover, .sidebar a.active {
    background: #e21b23;
}
.logout {
    background: #e21b23;
    color: white;
    margin-top: auto;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    width: 80%;
    font-size: 14px;
}

/* MAIN */
.main {
    margin-left: 210px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* TOPBAR */
.topbar {
    background: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.topbar h1 {
    margin: 0;
    color: #17345f;
    font-size: 20px;
}
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #17345f;
}

/* CONTENT */
.content {
    padding: 20px 25px;
    overflow-y: auto;
}
.message {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-weight: bold;
    text-align: center;
}
.message.success { background: #e7f3e7; color: #2d662d; }
.message.error { background: #ffe7e7; color: #8b0000; }

/* FORM */
form {
    background: white;
    padding: 25px;
    border-radius: 10px;
    max-width: 500px;
    margin: 30px auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input[type="text"], textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
textarea {
    resize: none;
}
button {
    background: #17345f;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}
button:hover {
    background: #e21b23;
}
</style>
</head>
<body>
<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Teacher Panel</h2>
  <a href="teacher-dashboard.php">🏠 Dashboard</a>
  <a href="attendance.php">📋 Mark Attendance</a>
  <a href="attendance_history.php">🕓 Attendance History</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="manage_students.php">👥 Manage Students</a>
  <a href="teacher_profile.php">👤 Profile</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>💬 Feedback Form</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($_SESSION['email']); ?></span>
             <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="message <?= str_contains($message, '✅') ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="message" rows="5" placeholder="Write your feedback or suggestion here..." required></textarea>
            <button type="submit">📨 Submit Feedback</button>
        </form>
    </div>
</div>
</body>
</html>
