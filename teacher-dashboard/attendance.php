<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

/* ---------- Fetch teacher info ---------- */
$teacher_name = $_SESSION['email'];
$profile_image = "../uploads/teachers/default.png"; // ✅ default fallback image

$stmt = $conn->prepare("SELECT full_name, profile_image FROM teacher_profiles WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (!empty($row['full_name'])) {
        $teacher_name = $row['full_name'];
    }
    if (!empty($row['profile_image']) && file_exists("../uploads/teachers/" . $row['profile_image'])) {
        $profile_image = "../uploads/teachers/" . $row['profile_image'];
    }
}
$stmt->close();

/* ---------- Fetch subjects for dropdown ---------- */
$subjects = [];
$stmt = $conn->prepare("SELECT id, subject_name, class_time FROM subjects WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

/* ---------- Handle selected subject ---------- */
$selected_subject_id = $_POST['subject_id'] ?? ($_GET['subject_id'] ?? '');
$selected_subject = null;
$class_time_str = null;

if ($selected_subject_id) {
    $stmt = $conn->prepare("SELECT subject_name, class_time FROM subjects WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $selected_subject_id, $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $selected_subject = $res->fetch_assoc();
    $stmt->close();

    if ($selected_subject) {
        $class_time_str = $selected_subject['class_time'];
    }
}

/* ---------- Fetch students for the subject ---------- */
$students = [];
if ($selected_subject_id) {
    $stmt = $conn->prepare("
        SELECT s.id, s.student_name
        FROM students s
        JOIN classes c ON s.class_id = c.id
        JOIN subjects sub ON c.id = sub.id
        WHERE sub.id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $selected_subject_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $students[] = $row;
        $stmt->close();
    }
}

/* ---------- Determine Attendance Window ---------- */
date_default_timezone_set('Asia/Manila');
$now = new DateTime();
$attendance_locked = true;
$seconds_until_unlock = 0;

if ($class_time_str) {
    $class_time = new DateTime($class_time_str);
    if ($class_time < $now) $class_time->modify('+1 day');
    $allowed_start = (clone $class_time)->modify('-30 minutes');
    $allowed_end = (clone $class_time)->modify('+15 minutes');
    $attendance_locked = ($now < $allowed_start || $now > $allowed_end);
    if ($now < $allowed_start) {
        $seconds_until_unlock = $allowed_start->getTimestamp() - $now->getTimestamp();
    }
}

/* ---------- Save Attendance ---------- */
$message = "";
if (isset($_POST['save_attendance']) && !$attendance_locked) {
    $date = date('Y-m-d');
    if (!empty($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $student_id => $status) {
            $stmt = $conn->prepare("
                INSERT INTO attendance (student_id, class_id, date, status)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("iiss", $student_id, $selected_subject_id, $date, $status);
                $stmt->execute();
                $stmt->close();
            }
        }
        $message = "✅ Attendance recorded successfully!";
    } else {
        $message = "⚠️ Please mark at least one student.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance | Teacher Dashboard</title>
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
    margin-top: auto;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    width: 80%;
}

/* MAIN */
.main {
    margin-left: 210px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
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

/* profile section in topbar */
.topbar .profile {
  display: flex;
  align-items: center;
  gap: 12px;
}
.profile-info {
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.profile-name {
  color: #17345f;
  font-weight: 600;
  font-size: 15px;
}
.wave { font-size: 16px; }
.profile-img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #17345f;
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}

/* CONTENT */
.content {
    padding: 20px 25px;
}
.message, .error {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.message { background: #e7f3e7; color: #2d662d; }
.error { background: #ffe7e7; color: #8b0000; }

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
th {
    background: #17345f;
    color: white;
}
tr:nth-child(even) { background: #f9f9f9; }

/* LOCK OVERLAY */
.lock-overlay {
    position: relative;
    background: rgba(255,255,255,0.9);
    text-align: center;
    padding: 20px;
    border: 2px dashed #e21b23;
    border-radius: 10px;
    margin-top: 20px;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="attendance.php" class="active">📊 Attendance</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Mark Attendance</h1>
        <div class="profile">
            <div class="profile-info">
                <span class="wave">👋</span>
                <span class="profile-name"><?= htmlspecialchars($teacher_name); ?></span>
            </div>
            <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label><b>Subject:</b></label>
            <select name="subject_id" onchange="this.form.submit()" required>
                <option value="">-- Select Subject --</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= $sub['id']; ?>" <?= $selected_subject_id == $sub['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($sub['subject_name']); ?> (<?= htmlspecialchars($sub['class_time']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selected_subject): ?>
            <h3>🗓 <?= htmlspecialchars($selected_subject['subject_name']); ?> — <?= htmlspecialchars($selected_subject['class_time']); ?></h3>

            <?php if ($attendance_locked): ?>
            <div class="lock-overlay" id="lockOverlay">
                <div>⚠️ Attendance will open in</div>
                <div id="countdown" style="font-size: 30px; color: #17345f; font-weight: bold;">--:--</div>
            </div>

            <script>
            let seconds = <?= $seconds_until_unlock ?>;
            const overlay = document.getElementById("lockOverlay");
            const countdownEl = document.getElementById("countdown");

            function updateCountdown() {
                if (seconds <= 0) {
                    overlay.style.display = "none";
                    return;
                }
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                countdownEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                seconds--;
                setTimeout(updateCountdown, 1000);
            }
            updateCountdown();
            </script>

            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="subject_id" value="<?= $selected_subject_id; ?>">
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                    </tr>
                    <?php if ($students): ?>
                        <?php foreach ($students as $stu): ?>
                        <tr>
                            <td><?= htmlspecialchars($stu['student_name']); ?></td>
                            <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Present" required></td>
                            <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Absent"></td>
                            <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Late"></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4"><i>No students found.</i></td></tr>
                    <?php endif; ?>
                </table>
                <br>
                <button type="submit" name="save_attendance">💾 Save Attendance</button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
