<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$date = $_GET['date'] ?? '';
$subject_name = $_GET['subject'] ?? '';

if (!$date || !$subject_name) {
    die("Invalid request.");
}

/* ✅ Fetch attendance details */
$stmt = $conn->prepare("
    SELECT s.student_name, a.status, a.created_at
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN subjects sub ON a.subject_id = sub.id
    WHERE a.date = ? AND sub.subject_name = ?
    ORDER BY s.student_name
");
$stmt->bind_param("ss", $date, $subject_name);
$stmt->execute();
$res = $stmt->get_result();
$records = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📋 Attendance Details | Attendify</title>
<style>
body {
  font-family: 'Segoe UI', Arial;
  background: #f4f6fa;
  margin: 0;
  padding: 25px;
}
.container {
  max-width: 1000px;
  margin: 0 auto;
}
h1 {
  color: #17345f;
  border-bottom: 2px solid #e21b23;
  padding-bottom: 5px;
}
table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
th {
  background: #17345f;
  color: white;
}
.status-present {
  color: green;
  font-weight: bold;
}
.status-absent {
  color: red;
  font-weight: bold;
}
.status-late {
  color: orange;
  font-weight: bold;
}
button {
  background: #17345f;
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 15px;
}
button:hover {
  background: #e21b23;
}
</style>
</head>
<body>
<div class="container">
  <h1>📅 Attendance Details — <?= htmlspecialchars($subject_name) ?> | <?= htmlspecialchars($date) ?></h1>

  <table>
    <tr>
      <th>Student Name</th>
      <th>Status</th>
      <th>Time Recorded</th>
    </tr>
    <?php if ($records): foreach ($records as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['student_name']) ?></td>
      <td>
        <?php
          $status = htmlspecialchars($r['status']);
          $class = strtolower($status) === 'present' ? 'status-present' :
                   (strtolower($status) === 'absent' ? 'status-absent' : 'status-late');
        ?>
        <span class="<?= $class ?>"><?= $status ?></span>
      </td>
      <td><?= htmlspecialchars($r['created_at']) ?></td>
    </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="3"><i>No attendance records found for this date.</i></td></tr>
    <?php endif; ?>
  </table>

  <a href="attendance_history.php"><button>⬅ Back to History</button></a>
</div>
</body>
</html>
