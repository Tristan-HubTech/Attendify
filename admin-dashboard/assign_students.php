<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';
include __DIR__ . '/admin_nav.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";

/* ================================
   ✅ Fetch all subjects (with teachers)
================================ */
$subjects = [];
$sub_query = "
  SELECT s.id, s.subject_name, s.class_time, t.full_name AS teacher_name
  FROM subjects s
  LEFT JOIN teacher_profiles t ON s.teacher_id = t.teacher_id
  ORDER BY s.subject_name
";
$res = $conn->query($sub_query);
while ($row = $res->fetch_assoc()) {
    $subjects[] = $row;
}

/* ================================
   ✅ Fetch all students
================================ */
$students = [];
$stu_query = "
  SELECT s.id AS student_id, s.student_name, u.email
  FROM students s
  JOIN users u ON s.user_id = u.id
  ORDER BY s.student_name
";
$res = $conn->query($stu_query);
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

/* ================================
   ✅ Handle form submission
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    $subject_id = intval($_POST['subject_id']);
    $selected_students = $_POST['students'] ?? [];

    // Clear old enrollments
    $conn->query("DELETE FROM enrollments WHERE subject_id = $subject_id");

    // Insert new ones
    foreach ($selected_students as $sid) {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $sid, $subject_id);
        $stmt->execute();
        $stmt->close();
    }

    $message = "✅ Student assignments updated successfully!";
    log_activity($conn, $admin_id, 'admin', 'Assign Students', "Assigned students to subject ID $subject_id");
}

/* ================================
   ✅ Fetch currently enrolled students (for the selected subject)
================================ */
$current_subject_id = $_POST['subject_id'] ?? null;
$current_enrollments = [];

if ($current_subject_id) {
    $stmt = $conn->prepare("SELECT student_id FROM enrollments WHERE subject_id = ?");
    $stmt->bind_param("i", $current_subject_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $current_enrollments[] = $r['student_id'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Students | Admin Dashboard</title>
<style>
body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f6fa; display:flex; height:100vh; }

/* SIDEBAR */
.sidebar { width:210px; background:#17345f; color:white; height:100vh; position:fixed; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar img { width:55%; margin-bottom:10px; }
.sidebar h2 { font-size:16px; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; color:white; text-decoration:none; padding:8px 15px; width:85%; text-align:left; border-radius:5px; margin:3px 0; font-size:14px; transition:0.3s; }
.sidebar a:hover, .sidebar a.active { background:#e21b23; }
.logout { background:#e21b23; color:white; margin-top:auto; margin-bottom:20px; text-align:center; border-radius:6px; padding:8px; width:80%; font-size:14px; }

/* MAIN */
.main { margin-left:210px; flex-grow:1; display:flex; flex-direction:column; }

/* TOPBAR */
.topbar { background:white; padding:12px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.topbar h1 { margin:0; color:#17345f; font-size:20px; }

/* CONTENT */
.content { padding:25px; overflow-y:auto; }
.message { background:#e7f3e7; color:#2d662d; padding:10px; border-radius:6px; margin-bottom:15px; text-align:center; }
.error { background:#ffe7e7; color:#8b0000; padding:10px; border-radius:6px; margin-bottom:15px; text-align:center; }

.form-container { background:white; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
h3 { color:#17345f; border-bottom:2px solid #e21b23; padding-bottom:5px; }

.student-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:8px; margin-top:15px; }
.student-item { background:#f4f6fa; padding:8px 10px; border-radius:6px; border:1px solid #ccc; }
button { background:#17345f; color:white; padding:10px 15px; border:none; border-radius:6px; margin-top:15px; cursor:pointer; }
button:hover { background:#e21b23; }
</style>
</head>
<body>

<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>

  <a href="admin.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_subjects.php">📘 Manage Subjects</a>
  <a href="manage_classes.php">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php" >🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php" >💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<!-- MAIN CONTENT -->
<div class="main">
  <div class="topbar">
    <h1>🎓 Assign Students to Subjects</h1>
  </div>

  <div class="content">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">
        <h3>Step 1: Select Subject</h3>
        <select name="subject_id" onchange="this.form.submit()" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc;">
          <option value="">-- Select Subject --</option>
          <?php foreach ($subjects as $s): ?>
            <option value="<?= $s['id']; ?>" <?= ($current_subject_id == $s['id']) ? 'selected' : ''; ?>>
              <?= htmlspecialchars($s['subject_name']); ?> — <?= htmlspecialchars($s['teacher_name'] ?: 'No teacher'); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <?php if ($current_subject_id): ?>
          <h3>Step 2: Select Students</h3>
          <div class="student-list">
            <?php foreach ($students as $stu): ?>
              <label class="student-item">
                <input type="checkbox" name="students[]" value="<?= $stu['student_id']; ?>" 
                  <?= in_array($stu['student_id'], $current_enrollments) ? 'checked' : ''; ?>>
                <?= htmlspecialchars($stu['student_name']); ?> <small>(<?= htmlspecialchars($stu['email']); ?>)</small>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit">💾 Save Assignments</button>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>
</body>
</html>
