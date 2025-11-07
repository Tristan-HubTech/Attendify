<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

/* ---------- Fetch teacher name & profile ---------- */
$teacher_name = $_SESSION['email'];
$profile_image = "../uploads/teachers/default.png";

// ✅ Safe prepare check (prevents bind_param error)
$stmt = $conn->prepare("SELECT full_name, profile_image FROM teacher_profiles WHERE teacher_id = ?");
if (!$stmt) {
    die("SQL Error: " . $conn->error); // Debugging helper
}
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (!empty($row['full_name'])) $teacher_name = $row['full_name'];
    if (!empty($row['profile_image']) && file_exists("../uploads/teachers/" . $row['profile_image'])) {
        $profile_image = "../uploads/teachers/" . $row['profile_image'];
    }
}
$stmt->close();

/* ---------- Fetch students ---------- */
$students = $conn->query("SELECT * FROM students ORDER BY student_name ASC");

/* ---------- Determine Attendance Window ---------- */
date_default_timezone_set('Asia/Manila'); // 🇵🇭 Set your timezone
$now = new DateTime();
$class_time = new DateTime('09:00'); // 🕘 Your class time
$allowed_start = (clone $class_time)->modify('-30 minutes');
$allowed_end = (clone $class_time)->modify('+15 minutes');
$attendance_locked = ($now < $allowed_start || $now > $allowed_end);

/* ---------- Save attendance ---------- */
$message = "";
if (isset($_POST['save_attendance'])) {
    if ($attendance_locked) {
        $message = "⚠️ Attendance is currently locked. You can only take attendance 30 minutes before class.";
    } else {
        $date = $_POST['date'] ?? '';
        if (!empty($_POST['attendance'])) {
            foreach ($_POST['attendance'] as $student_id => $status) {
                $stmt = $conn->prepare("INSERT INTO attendance (student_id, teacher_id, date, status) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    die("SQL Insert Error: " . $conn->error);
                }
                $stmt->bind_param("iiss", $student_id, $teacher_id, $date, $status);
                $stmt->execute();
                $stmt->close();
            }
            $message = "✅ Attendance saved successfully!";
        } else {
            $message = "⚠️ Please mark attendance for at least one student.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ACLC Attendance Dashboard</title>
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
.sidebar a:hover { background: #e21b23; }
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
    position: relative;
}
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
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

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    font-size: 14px;
}
th {
    background: #17345f;
    color: white;
}
tr:nth-child(even) { background: #f9f9f9; }
input[type="submit"] {
    background: #17345f;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
input[type="submit"]:hover { background: #e21b23; }

/* LOCK OVERLAY */
.lock-overlay {
    position: absolute;
    top: 120px;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    color: #8b0000;
    font-weight: bold;
    border: 2px dashed #e21b23;
    border-radius: 8px;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
     <a href="attendance.php">📊 Attendance</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="manage_subjects.php">📘 Manage Subjects</a>
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Attendance Management</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($teacher_name); ?></span>
            <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <h3>🗓 Take Attendance</h3>
        <form method="POST">
            <label>Date:</label>
            <input type="date" name="date" required>
            
            <?php if ($students && $students->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                    </tr>
                    <?php while ($stu = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($stu['student_name']); ?></td>
                        <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Present" <?= $attendance_locked ? 'disabled' : '' ?> required></td>
                        <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Absent" <?= $attendance_locked ? 'disabled' : '' ?>></td>
                        <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Late" <?= $attendance_locked ? 'disabled' : '' ?>></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <input type="submit" name="save_attendance" value="💾 Save Attendance" <?= $attendance_locked ? 'disabled' : '' ?>>
            <?php else: ?>
                <p><i>No students found.</i></p>
            <?php endif; ?>
        </form>

        <?php if ($attendance_locked): ?>
        <div class="lock-overlay">
            ⚠️ Attendance will open 30 minutes before class time.
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
