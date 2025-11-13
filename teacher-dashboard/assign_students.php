<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// ✅ Restrict to teachers
if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$message = "";

/* ================================
   ✅ Fetch subjects for this teacher
================================ */
$subjects = [];
$stmt = $conn->prepare("SELECT id, subject_name, class_time FROM subjects WHERE teacher_id = ? ORDER BY subject_name");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $subjects[] = $row;
$stmt->close();

/* ================================
   ✅ Fetch all students
================================ */
$students = [];
$res = $conn->query("SELECT id AS student_id, student_name, email FROM students ORDER BY student_name");
while ($row = $res->fetch_assoc()) $students[] = $row;

/* ================================
   ✅ Handle Assign Form
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    $subject_id = intval($_POST['subject_id']);
    $selected_students = $_POST['students'] ?? [];

    // Get current enrollments
    $current = [];
    $stmt = $conn->prepare("SELECT student_id FROM enrollments WHERE subject_id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $current[] = $r['student_id'];
    $stmt->close();

    // ✅ Enroll new students
    foreach ($selected_students as $sid) {
        if (!in_array($sid, $current)) {
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $sid, $subject_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // ✅ Unenroll unchecked students
    foreach ($current as $sid) {
        if (!in_array($sid, $selected_students)) {
            $stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ? AND subject_id = ?");
            $stmt->bind_param("ii", $sid, $subject_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    $message = "✅ Student assignments permanently updated!";
    log_activity($conn, $teacher_id, 'teacher', 'Assign Students', "Updated permanent enrollments for subject ID: $subject_id");
}

/* ================================
   ✅ Fetch current enrollments
================================ */
$current_subject_id = $_POST['subject_id'] ?? null;
$current_enrollments = [];
if ($current_subject_id) {
    $stmt = $conn->prepare("SELECT student_id FROM enrollments WHERE subject_id = ?");
    $stmt->bind_param("i", $current_subject_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $current_enrollments[] = $r['student_id'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Students | Teacher Dashboard</title>
<style>
body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f6fa; display:flex; height:100vh; }
.sidebar { width:210px; background:#17345f; color:white; height:100vh; position:fixed; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar img { width:55%; margin-bottom:10px; }
.sidebar h2 { font-size:16px; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; color:white; text-decoration:none; padding:8px 15px; width:85%; text-align:left; border-radius:5px; margin:3px 0; font-size:14px; transition:0.3s; }
.sidebar a:hover, .sidebar a.active { background:#e21b23; }
.logout { background:#e21b23; color:white; margin-top:auto; margin-bottom:20px; text-align:center; border-radius:6px; padding:8px; width:80%; font-size:14px; }

.main { margin-left:210px; flex-grow:1; display:flex; flex-direction:column; }
.topbar { background:white; padding:12px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.topbar h1 { margin:0; color:#17345f; font-size:20px; }

.content { padding:25px; overflow-y:auto; }
.message { background:#e7f3e7; color:#2d662d; padding:10px; border-radius:6px; margin-bottom:15px; text-align:center; }
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
    <h1>🎓 Assign Students to Subjects</h1>
  </div>

  <div class="content">
    <?php if ($message): ?>
       <span>👋 <?= htmlspecialchars($_SESSION['email']); ?></span>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="form-container">
      <form method="POST">
        <h3>Step 1: Select Subject</h3>
        <select name="subject_id" onchange="this.form.submit()" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc;">
          <option value="">-- Select Subject --</option>
          <?php foreach ($subjects as $s): ?>
            <option value="<?= $s['id']; ?>" <?= ($current_subject_id == $s['id']) ? 'selected' : ''; ?>>
              <?= htmlspecialchars($s['subject_name']); ?> (<?= htmlspecialchars($s['class_time']); ?>)
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
