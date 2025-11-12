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
$teacher_name = "Teacher";
$profile_image = "../uploads/teachers/default.png";
$message = "";
$selected_subject_id = $_POST['subject_id'] ?? null;

/* ✅ Fetch teacher info */
$stmt = $conn->prepare("SELECT full_name, profile_image FROM teacher_profiles WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $teacher_name = $row['full_name'];
    if (!empty($row['profile_image']) && file_exists("../uploads/teachers/" . $row['profile_image'])) {
        $profile_image = "../uploads/teachers/" . $row['profile_image'];
    }
}
$stmt->close();

/* ✅ Fetch Subjects */
$subjects = [];
$stmt = $conn->prepare("SELECT id, subject_name, class_time FROM subjects WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $subjects[] = $row;
$stmt->close();

/* ✅ Save Attendance */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $subject_id = intval($_POST['subject_id']);
    $date = date("Y-m-d");
    $statuses = $_POST['attendance'] ?? [];
    $count = 0;

    foreach ($statuses as $student_id => $status) {
        $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND date = ?");
        $check->bind_param("iis", $student_id, $subject_id, $date);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("iiss", $student_id, $subject_id, $date, $status);
            $insert->execute();
            $insert->close();
            $count++;
        }
        $check->close();
    }

    $message = "✅ Attendance saved for today ($count records)";
}

/* ✅ Fetch Students */
$students = [];
if ($selected_subject_id) {
    $stmt = $conn->prepare("
        SELECT s.id, s.student_name 
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.subject_id = ?
        ORDER BY s.student_name
    ");
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
<title>Mark Attendance | Attendify</title>
<style>
body { margin: 0; font-family: 'Segoe UI', Arial; background: #f4f6fa; display: flex; height: 100vh; }
.sidebar { width: 210px; background: #17345f; color: white; height: 100vh; position: fixed; display: flex; flex-direction: column; align-items: center; padding-top: 15px; }
.sidebar img { width: 60%; margin-bottom: 10px; }
.sidebar h2 { font-size: 16px; margin-bottom: 15px; }
.sidebar a { display: block; color: white; text-decoration: none; padding: 8px 15px; width: 85%; text-align: left; border-radius: 5px; margin: 3px 0; font-size: 14px; transition: 0.3s; }
.sidebar a:hover { background: #e21b23; }
.sidebar .active { background: #e21b23; }
.logout { background: #e21b23; margin-top: auto; margin-bottom: 20px; border-radius: 6px; padding: 8px; width: 80%; text-align: center; }

.main { margin-left: 210px; flex-grow: 1; display: flex; flex-direction: column; }

/* TOPBAR */
.topbar { background: white; padding: 12px 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.topbar h1 { color: #17345f; margin: 0; font-size: 20px; }
.profile { display: flex; align-items: center; gap: 10px; }
.profile img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #17345f; object-fit: cover; }

/* CONTENT */
.content { padding: 25px; overflow-y: auto; }
.message { background: #e7f3e7; color: #2d662d; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #17345f; color: white; }
button { background: #17345f; color: white; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; }
button:hover { background: #e21b23; }
</style>
</head>
<body>
<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="teacher-dashboard.php" class="active">🏠 Dashboard</a>
    <a href="attendance.php">📊 Attendance</a>
    <a href="assign_students.php">🎓 Assign Students</a>
    <a href="manage_students.php">👥 Manage Students</a>
    <a href="feedback.php">💬 Feedback</a>
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<div class="main">
  <div class="topbar">
    <h1>📋 Mark Attendance</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($teacher_name); ?></span>
      <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile">
    </div>
  </div>

  <div class="content">
    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

    <form method="POST">
      <label><b>Select Subject:</b></label>
      <select name="subject_id" onchange="this.form.submit()" required>
        <option value="">-- Choose Subject --</option>
        <?php foreach ($subjects as $sub): ?>
          <option value="<?= $sub['id'] ?>" <?= ($selected_subject_id == $sub['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($sub['subject_name']); ?> (<?= htmlspecialchars($sub['class_time']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($selected_subject_id): ?>
    <form method="POST">
      <input type="hidden" name="subject_id" value="<?= $selected_subject_id; ?>">
      <p><b>Date:</b> <?= date("F j, Y"); ?></p>
      <table>
        <tr><th>Student</th><th>Present</th><th>Absent</th><th>Late</th></tr>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['student_name']); ?></td>
          <td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="Present" required></td>
          <td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="Absent"></td>
          <td><input type="radio" name="attendance[<?= $s['id'] ?>]" value="Late"></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <br><button type="submit" name="save_attendance">💾 Save Attendance</button>
    </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
