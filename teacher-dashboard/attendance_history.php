<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = "Teacher";
$profile_image = "../uploads/teachers/default.png";
$filter_date = $_GET['date'] ?? '';
$filter_subject = $_GET['subject'] ?? '';

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

/* ✅ Fetch subjects for dropdown */
$subjects = [];
$stmt = $conn->prepare("SELECT id, subject_name FROM subjects WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $subjects[] = $row;
$stmt->close();

/* ✅ Fetch attendance summary (with filters) */
$query = "
    SELECT a.date, sub.subject_name, COUNT(DISTINCT a.student_id) AS total_students
    FROM attendance a
    JOIN subjects sub ON a.subject_id = sub.id
    WHERE sub.teacher_id = ?
";
$params = [$teacher_id];
$types = "i";

if (!empty($filter_date)) {
    $query .= " AND a.date = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if (!empty($filter_subject)) {
    $query .= " AND sub.subject_name = ?";
    $params[] = $filter_subject;
    $types .= "s";
}

$query .= " GROUP BY a.date, sub.subject_name ORDER BY a.date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$records = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📅 Attendance History | Attendify</title>
<style>
body { margin: 0; font-family: 'Segoe UI', Arial; background: #f4f6fa; display: flex; height: 100vh; }
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
  width: 60%;
  margin-bottom: 10px;
}

.sidebar h2 {
  font-size: 16px;
  margin-bottom: 15px;
}

.sidebar a {
  display: block;
  color: white;
  text-decoration: none;
  padding: 10px 15px; /* ✅ uniform padding */
  width: 85%;
  text-align: left;
  border-radius: 5px;
  margin: 4px 0; /* ✅ even spacing */
  font-size: 14px;
  transition: 0.3s;
  height: auto; /* ✅ prevents stretching */
  line-height: 20px; /* ✅ consistent vertical alignment */
}

.sidebar a:hover {
  background: #e21b23;
}

.sidebar .active {
  background: #e21b23;
}

.logout {
  background: #e21b23;
  margin-top: auto;
  margin-bottom: 20px;
  border-radius: 6px;
  padding: 10px 15px; /* ✅ same as others */
  width: 80%;
  text-align: center;
  font-size: 14px;
}

.logout { background: #e21b23; margin-top: auto; margin-bottom: 20px; border-radius: 6px; padding: 8px; width: 80%; text-align: center; }
.main { margin-left: 210px; flex-grow: 1; display: flex; flex-direction: column; }

/* Topbar */
.topbar { background: white; padding: 12px 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.topbar h1 { color: #17345f; margin: 0; font-size: 20px; }
.profile { display: flex; align-items: center; gap: 10px; }
.profile img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #17345f; object-fit: cover; }

/* Content */
.content { padding: 25px; overflow-y: auto; }
form.filter-form { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
select, input[type="date"] { padding: 6px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
button {
  background: #17345f;
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
button:hover { background: #e21b23; }

table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #17345f; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
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
    <h1>🕓 Attendance History (Daily Summary)</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($teacher_name); ?></span>
      <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile">
    </div>
  </div>

  <div class="content">
    <!-- ✅ Filter Form -->
    <form method="GET" class="filter-form">
      <label><b>Date:</b></label>
      <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">

      <label><b>Subject:</b></label>
      <select name="subject">
        <option value="">-- All Subjects --</option>
        <?php foreach ($subjects as $sub): ?>
          <option value="<?= htmlspecialchars($sub['subject_name']) ?>" <?= ($filter_subject == $sub['subject_name']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($sub['subject_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">🔍 Filter</button>
      <a href="attendance_history.php"><button type="button">🔄 Reset</button></a>
    </form>

    <!-- ✅ Attendance Table -->
    <table>
      <tr>
        <th>Date</th>
        <th>Subject</th>
        <th>Total Students Marked</th>
        <th>Action</th>
      </tr>
      <?php if ($records): foreach ($records as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><?= htmlspecialchars($r['subject_name']) ?></td>
        <td><?= htmlspecialchars($r['total_students']) ?></td>
        <td>
          <form action="attendance_details.php" method="GET" style="margin:0;">
            <input type="hidden" name="date" value="<?= htmlspecialchars($r['date']) ?>">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($r['subject_name']) ?>">
            <button type="submit">View Details</button>
          </form>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="4"><i>No attendance records found.</i></td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
</body>
</html>
