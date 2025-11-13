<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// Helper: safe prepare with error handling
function safe_prepare($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $err = date("Y-m-d H:i:s") . " | SQL Prepare Error: " . $conn->error . " | Query: " . $sql . PHP_EOL;
        @file_put_contents(__DIR__ . "/../logs/sql_errors.log", $err, FILE_APPEND);
        return false;
    }
    return $stmt;
}

// Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = intval($_SESSION['user_id']);
$message = "";
$selected_subject_id = $_POST['subject_id'] ?? ($_GET['subject_id'] ?? null);

// =========================
// Fetch teacher info safely
// =========================
$teacher_name = 'Teacher';
$profile_image = "../uploads/teachers/default.png";
$sql = "SELECT full_name, profile_image FROM teacher_profiles WHERE teacher_id = ?";
$stmt = safe_prepare($conn, $sql);
if ($stmt !== false) {
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
} else {
    $message = "⚠️ Warning: Could not load teacher profile (check logs).";
}

// =========================
// Fetch subjects safely
// =========================
$subjects = [];
$sql = "SELECT id, subject_name, class_time FROM subjects WHERE teacher_id = ?";
$stmt = safe_prepare($conn, $sql);
if ($stmt !== false) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $message = "⚠️ Warning: Could not load subjects.";
}

// =========================
// Handle remove student
// =========================
if (isset($_GET['remove']) && isset($_GET['subject'])) {
    $student_id = intval($_GET['remove']);
    $subject_id = intval($_GET['subject']);
    $sql = "DELETE FROM enrollments WHERE student_id = ? AND subject_id = ?";
    $stmt = safe_prepare($conn, $sql);
    if ($stmt !== false) {
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        $stmt->close();
        header("Location: attendance.php?subject_id=" . $subject_id . "&msg=removed");
        exit();
    } else {
        $message = "❌ Failed to remove student.";
    }
}

// =========================
// Handle attendance save
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $subject_id = intval($_POST['subject_id']);
    $date = date("Y-m-d");
    $statuses = $_POST['attendance'] ?? [];
    $count = 0;
    $sms_sent = 0;

    if (empty($statuses)) {
        $message = "⚠️ No attendance selections were made.";
    } else {
        foreach ($statuses as $student_id => $status) {
            $student_id = intval($student_id);
            $status = substr(trim($status), 0, 50);

            // Check if entry exists
            $sql = "SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND date = ?";
            $check = safe_prepare($conn, $sql);
            if ($check === false) continue;
            $check->bind_param("iis", $student_id, $subject_id, $date);
            $check->execute();
            $check->store_result();

            if ($check->num_rows === 0) {
                // Insert
                $sql = "INSERT INTO attendance (student_id, subject_id, date, status, created_at) VALUES (?, ?, ?, ?, NOW())";
                $insert = safe_prepare($conn, $sql);
                if ($insert !== false) {
                    $insert->bind_param("iiss", $student_id, $subject_id, $date, $status);
                    $insert->execute();
                    $insert->close();
                    $count++;
                }
            } else {
                // Update
                $sql = "UPDATE attendance SET status = ?, updated_at = NOW() WHERE student_id = ? AND subject_id = ? AND date = ?";
                $update = safe_prepare($conn, $sql);
                if ($update !== false) {
                    $update->bind_param("siis", $status, $student_id, $subject_id, $date);
                    $update->execute();
                    $update->close();
                    $count++;
                }
            }
            $check->close();

            // Fetch parent phone
            $sql = "SELECT student_name, parent_phone FROM students WHERE id = ?";
            $s = safe_prepare($conn, $sql);
            if ($s === false) continue;
            $s->bind_param("i", $student_id);
            $s->execute();
            $student = $s->get_result()->fetch_assoc();
            $s->close();

            $parent_phone = $student['parent_phone'] ?? '';

            if ($parent_phone !== '') {
                // Send SMS through send_sms.php
                $sms_url = "http://localhost/Attendify/teacher-dashboard/send_sms.php";
                $postData = [
                    'student_id' => $student_id,
                    'status' => $status
                ];

                $ch = curl_init($sms_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                $response = curl_exec($ch);
                curl_close($ch);

                $sms_sent++;
            }
        }

        $message = "✅ Attendance saved ($count records). SMS sent: $sms_sent.";
    }
}

// =========================
// Fetch enrolled students WITH PROFILE IMAGE
// =========================
$students = [];
if ($selected_subject_id) {
    $sql = "
        SELECT s.id, s.student_name, s.parent_phone, s.profile_image
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.subject_id = ?
        ORDER BY s.student_name
    ";
    $stmt = safe_prepare($conn, $sql);
    if ($stmt !== false) {
        $stmt->bind_param("i", $selected_subject_id);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $message = "⚠️ Could not load enrolled students.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>📋 Mark Attendance | Attendify</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f6fa; margin: 0; }
.sidebar { width: 210px; background: #17345f; color: white; height: 100vh; position: fixed; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar a { color:white; text-decoration:none; display:block; width:85%; padding:8px 15px; margin:3px 0; border-radius:5px; }
.sidebar a:hover, .sidebar .active { background: #e21b23; }
.main { margin-left:210px; padding:25px; }
table { width:100%; border-collapse:collapse; background:white; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
th,td { padding:10px; border:1px solid #ddd; text-align:center; }
th { background:#17345f; color:white; }
img.pfp { width:45px; height:45px; border-radius:50%; object-fit:cover; }
button { background:#17345f; color:white; border:none; border-radius:6px; padding:8px 15px; cursor:pointer; }
button:hover { background:#e21b23; }
.warning { color:red; font-weight:bold; }
.message { background:#e7f3e7; color:#2d662d; padding:10px; border-radius:5px; margin-bottom:10px; }
.remove-btn { background:#c62828; color:white; border-radius:6px; padding:6px 10px; cursor:pointer; }
.remove-btn:hover { background:#a51616; }
</style>
</head>
<body>

<div class="sidebar">
  <img src="../ama.png" width="80%">
  <h2>Teacher Panel</h2>
  <a href="teacher-dashboard.php">🏠 Dashboard</a>
  <a href="attendance.php" class="active">📋 Mark Attendance</a>
  <a href="attendance_history.php">🕓 Attendance History</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="manage_students.php">👥 Manage Students</a>
  <a href="teacher_profile.php">👤 Profile</a>
  <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">
  <h1>📋 Mark Attendance</h1>

  <?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST">
    <label><b>Select Subject:</b></label>
    <select name="subject_id" onchange="this.form.submit()" required>
      <option value="">-- Choose Subject --</option>
      <?php foreach ($subjects as $sub): ?>
        <option value="<?= htmlspecialchars($sub['id']) ?>" <?= ($selected_subject_id == $sub['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($sub['subject_name']) ?> (<?= htmlspecialchars($sub['class_time']) ?>)
        </option>
      <?php endforeach; ?>
    </select>

    <?php if ($selected_subject_id): ?>
      <p><b>Date:</b> <?= date("F j, Y"); ?></p>

      <table>
        <tr>
          <th>Profile</th>
          <th>Student</th>
          <th>Parent Contact</th>
          <th>Present</th>
          <th>Absent</th>
          <th>Late</th>
          <th>Remove</th>
        </tr>

        <?php if ($students): foreach ($students as $s): ?>
          <tr>
            <td>
              <img class="pfp" src="../uploads/students/<?= htmlspecialchars($s['profile_image'] ?: 'default.png') ?>">
            </td>

            <td><?= htmlspecialchars($s['student_name']); ?></td>

            <td>
              <?= empty($s['parent_phone'])
                ? '<span class="warning">⚠️ No number</span>'
                : htmlspecialchars($s['parent_phone']); ?>
            </td>

            <td><input type="radio" name="attendance[<?= intval($s['id']) ?>]" value="Present"></td>
            <td><input type="radio" name="attendance[<?= intval($s['id']) ?>]" value="Absent"></td>
            <td><input type="radio" name="attendance[<?= intval($s['id']) ?>]" value="Late"></td>

            <td>
              <button type="button" class="remove-btn"
                onclick="removeStudent(<?= intval($s['id']) ?>, <?= intval($selected_subject_id) ?>)">
                🗑️
              </button>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7"><i>No students enrolled for this subject.</i></td></tr>
        <?php endif; ?>

      </table>

      <br>
      <button type="submit" name="save_attendance">💾 Save Attendance</button>
    <?php endif; ?>
  </form>
</div>

<script>
function removeStudent(studentId, subjectId) {
  if (confirm("Remove this student from the subject?")) {
    window.location.href =
      "attendance.php?remove=" + studentId + "&subject=" + subjectId;
  }
}
</script>

</body>
</html>
