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
$teacher_name = "Teacher User";
$selected_subject_id = null;
$message = "";

/* ================================
   ✅ Fetch Teacher Info
================================ */
$stmt = $conn->prepare("SELECT full_name FROM teacher_profiles WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $teacher_name = $row['full_name'];
$stmt->close();

/* ✅ Fetch Profile Image */
$profile_image = "../uploads/teachers/default.png";
$stmt = $conn->prepare("SELECT profile_image FROM teacher_profiles WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (!empty($row['profile_image']) && file_exists("../uploads/teachers/" . $row['profile_image'])) {
        $profile_image = "../uploads/teachers/" . $row['profile_image'];
    }
}
$stmt->close();

/* ✅ Log Page Visit */
log_activity($conn, $teacher_id, 'teacher', 'View Attendance Page', 'Teacher accessed attendance page.');

/* ✅ Handle Subject Selection */
if (isset($_POST['subject_id'])) {
    $selected_subject_id = intval($_POST['subject_id']);
}

/* ================================
   ✅ Fetch Subjects Assigned to Teacher
================================ */
$subjects = [];
$subject_query = $conn->prepare("SELECT id, subject_name, class_time FROM subjects WHERE teacher_id = ?");
$subject_query->bind_param("i", $teacher_id);
$subject_query->execute();
$res = $subject_query->get_result();
while ($row = $res->fetch_assoc()) $subjects[] = $row;
$subject_query->close();

/* ================================
   ✅ Handle Attendance Submission + Sms8.io
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {

    $subject_id = intval($_POST['subject_id']);
    $attendance_date = date("Y-m-d");
    $statuses = $_POST['attendance'] ?? [];

    foreach ($statuses as $student_id => $status) {
        // ✅ Save Attendance
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, subject_id, date, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");
        if ($stmt) {
            $stmt->bind_param("iiss", $student_id, $subject_id, $attendance_date, $status);
            $stmt->execute();
            $stmt->close();
        }

        // ✅ Send SMS if Absent using Sms8.io
        if ($status === 'Absent') {
            $getPhone = $conn->prepare("SELECT phone, student_name FROM students WHERE id = ?");
            $getPhone->bind_param("i", $student_id);
            $getPhone->execute();
            $result = $getPhone->get_result();
            if ($row = $result->fetch_assoc()) {
                $to = $row['phone']; // e.g. +639XXXXXXXXX
                $student_name = $row['student_name'];
                $date_today = date("Y-m-d");

                // ✅ Sms8.io Configuration
                $api_key = "ba176e34302a4e16687e4bb5d7c286d26dcfbe95";
                $message = "Attendify Notice: $student_name was marked ABSENT on $date_today. Please contact the teacher if this is incorrect.";
                $encoded_message = urlencode($message);

                // ✅ Use the Front API
                $url = "https://app.sms8.io/services/sendFront.php?key={$api_key}&number={$to}&message={$encoded_message}";

                // Send API Request
                $response = @file_get_contents($url);

                // Log success or failure
                if ($response === FALSE) {
                    error_log("❌ Sms8.io failed to send message to $to");
                } else {
                    error_log("✅ Sms8.io sent message successfully to $to");
                }
            }
            $getPhone->close();
        }
    }

    $message = "✅ Attendance marked successfully and SMS sent for absentees!";
    log_activity($conn, $teacher_id, 'teacher', 'Mark Attendance', "Marked attendance for Subject ID: $subject_id on $attendance_date");
}

/* ================================
   ✅ Fetch Students for Selected Subject
================================ */
$students = [];
if ($selected_subject_id) {
    $query = "
        SELECT s.id AS student_id, s.student_name
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.subject_id = ?
        ORDER BY s.student_name
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_subject_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $students[] = $row;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance | Teacher Dashboard</title>
<style>
body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f6fa; display:flex; height:100vh; }

/* SIDEBAR */
.sidebar { width:210px; background:#17345f; color:white; height:100vh; position:fixed; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar img { width:55%; margin-bottom:10px; border-radius:5px; }
.sidebar h2 { font-size:16px; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; color:white; text-decoration:none; padding:8px 15px; width:85%; text-align:left; border-radius:5px; margin:3px 0; font-size:14px; transition:0.3s; }
.sidebar a:hover { background:#e21b23; }
.logout { background:#e21b23; margin-top:auto; margin-bottom:20px; text-align:center; border-radius:6px; padding:8px; width:80%; }

/* MAIN */
.main { margin-left:210px; flex-grow:1; display:flex; flex-direction:column; }

/* TOPBAR */
.topbar { background:white; padding:12px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.topbar h1 { margin:0; color:#17345f; font-size:20px; }

/* PROFILE */
.topbar .profile { display:flex; align-items:center; gap:12px; }
.profile-info { display:flex; align-items:center; gap:8px; }
.profile-name { color:#17345f; font-weight:600; font-size:15px; }
.wave { font-size:16px; }
.profile-img { width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid #17345f; box-shadow:0 2px 6px rgba(0,0,0,0.12); }

/* CONTENT */
.content { padding:20px 25px; }
.message,.error { padding:10px; border-radius:5px; margin-bottom:15px; }
.message { background:#e7f3e7; color:#2d662d; }
.error { background:#ffe7e7; color:#8b0000; }

/* TABLE */
table { width:100%; border-collapse:collapse; margin-top:15px; }
th,td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#17345f; color:white; }
tr:nth-child(even){ background:#f9f9f9; }
button { background:#17345f; color:white; padding:8px 15px; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#e21b23; }

/* POPUP */
.popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px 30px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  text-align: center;
  display: none;
  z-index: 999;
}
.popup.success { border-left: 6px solid #2ecc71; }
.popup.error { border-left: 6px solid #e74c3c; }
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="attendance.php">📊 Attendance</a>
    <a href="manage_students.php">👥 Manage Students</a>
    <a href="assign_students.php" >🎓 Assign Students</a>
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="feedback.php">💬 Feedback</a>
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
            <script>
                window.onload = function() {
                    const popup = document.createElement('div');
                    popup.className = 'popup success';
                    popup.innerHTML = '<h3><?= addslashes($message); ?></h3>';
                    document.body.appendChild(popup);
                    popup.style.display = 'block';
                    setTimeout(() => popup.style.display = 'none', 4000);
                }
            </script>
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

        <?php if ($selected_subject_id): ?>
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
                            <td><input type="radio" name="attendance[<?= $stu['student_id']; ?>]" value="Present" required></td>
                            <td><input type="radio" name="attendance[<?= $stu['student_id']; ?>]" value="Absent"></td>
                            <td><input type="radio" name="attendance[<?= $stu['student_id']; ?>]" value="Late"></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4"><i>No students enrolled in this subject.</i></td></tr>
                    <?php endif; ?>
                </table>
                <br>
                <button type="submit" name="save_attendance">💾 Save Attendance</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
