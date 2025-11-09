<?php
session_start();
require '../db_connect.php';
require '../log_activity.php'; // ✅ Include activity log system

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

/* ---------- Fetch teacher name ---------- */
$teacher_name = $_SESSION['email']; // default fallback
$stmt = $conn->prepare("SELECT full_name FROM teacher_profiles WHERE teacher_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $teacher_name = $row['full_name'];
    }
    $stmt->close();
}

// ✅ Log that teacher opened Manage Subjects page
log_activity($conn, $teacher_id, 'teacher', 'View Manage Subjects', 'Teacher viewed the Manage Subjects page.');

/* ---------- Add new subject ---------- */
if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    $class_time = $_POST['class_time'] ?? '08:00:00';

    if ($subject_name !== '') {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, class_time, teacher_id) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $subject_name, $class_time, $teacher_id);
            if ($stmt->execute()) {
                $message = "✅ Subject added successfully.";
                // ✅ Log activity
                log_activity($conn, $teacher_id, 'teacher', 'Add Subject', "Added new subject: $subject_name at $class_time");
            } else {
                $message = "❌ Failed to add subject: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Database prepare failed: " . $conn->error;
        }
    } else {
        $message = "⚠️ Please enter a subject name.";
    }
}

/* ---------- Delete subject ---------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Fetch subject name for logging before deletion
    $fetch = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ? AND teacher_id = ?");
    $fetch->bind_param("ii", $id, $teacher_id);
    $fetch->execute();
    $res = $fetch->get_result();
    $subject_row = $res->fetch_assoc();
    $subject_name = $subject_row['subject_name'] ?? 'Unknown Subject';
    $fetch->close();

    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ? AND teacher_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $id, $teacher_id);
        if ($stmt->execute()) {
            $message = "🗑️ Subject deleted successfully.";
            // ✅ Log delete activity
            log_activity($conn, $teacher_id, 'teacher', 'Delete Subject', "Deleted subject: $subject_name (ID: $id)");
        } else {
            $message = "❌ Failed to delete subject: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "❌ Failed to prepare delete statement: " . $conn->error;
    }
}

/* ---------- Fetch subjects ---------- */
$subjects = [];
$stmt = $conn->prepare("SELECT * FROM subjects WHERE teacher_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $subjects[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects</title>
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
    background: #e7f3e7;
    color: #2d662d;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.error {
    background: #ffe7e7;
    color: #8b0000;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
    font-size: 14px;
}
th {
    background: #17345f;
    color: white;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
input[type="text"], input[type="time"] {
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 90%;
}
button {
    background: #17345f;
    color: white;
    padding: 6px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #e21b23;
}
.delete {
    background: #e21b23;
}
.delete:hover {
    background: #b91c1c;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="attendance.php">📊 Attendance</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="feedback.php" class="active">💬 Feedback</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Manage Subjects</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($teacher_name); ?></span>
            <img src="../uploads/teachers/default.png" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <h3>➕ Add Subject</h3>
        <form method="POST">
            <input type="text" name="subject_name" placeholder="Subject Name" required>
            <input type="time" name="class_time" required>
            <button type="submit" name="add_subject">Add</button>
        </form>

        <h3>📋 Current Subjects</h3>
        <?php if (count($subjects) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Subject Name</th>
                <th>Class Time</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($subjects as $sub): ?>
            <tr>
                <td><?= $sub['id']; ?></td>
                <td><?= htmlspecialchars($sub['subject_name']); ?></td>
                <td><?= htmlspecialchars($sub['class_time']); ?></td>
                <td>
                    <a href="?delete=<?= $sub['id']; ?>" onclick="return confirm('Delete this subject?')">
                        <button type="button" class="delete">🗑️ Delete</button>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p><i>No subjects found yet.</i></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
