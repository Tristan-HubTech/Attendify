<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// Ensure student has profile
$stmt = $conn->prepare("SELECT id, name, section FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: profile.php");
    exit();
}

$student_id = $student['id'];

// Attendance summary
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
$q = $conn->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ? GROUP BY status");
$q->bind_param("i", $student_id);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $summary[$row['status']] = $row['count'];
}
$q->close();

// Recent attendance list
$list = $conn->prepare("
    SELECT a.date, s.name AS subject_name, a.status 
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC LIMIT 20
");
$list->bind_param("i", $student_id);
$list->execute();
$records = $list->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<style>
body {font-family: Arial, sans-serif; background:#f8f9fa; margin:0;}
.container {width:90%; margin:30px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h1 {color:#1d3557;}
.summary {display:flex; gap:20px;}
.card {flex:1; background:#457b9d; color:white; padding:15px; border-radius:10px; text-align:center;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th,td {border:1px solid #ccc; padding:8px; text-align:center;}
th {background:#1d3557; color:white;}
.logout {float:right; background:#e63946; color:white; padding:8px 15px; border-radius:5px; text-decoration:none;}
.logout:hover {background:#d62828;}
</style>
</head>
<body>
<div class="container">
<a href="../logout.php" class="logout">Logout</a>
<h1>Welcome, <?= htmlspecialchars($student['name']); ?></h1>
<p>Section: <?= htmlspecialchars($student['section']); ?></p>

<div class="summary">
    <div class="card"><h3>Present</h3><p><?= $summary['Present']; ?></p></div>
    <div class="card"><h3>Absent</h3><p><?= $summary['Absent']; ?></p></div>
    <div class="card"><h3>Late</h3><p><?= $summary['Late']; ?></p></div>
</div>

<h2>Recent Attendance Records</h2>
<table>
<tr><th>Date</th><th>Subject</th><th>Status</th></tr>
<?php while ($row = $records->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['date']); ?></td>
    <td><?= htmlspecialchars($row['subject_name']); ?></td>
    <td><?= htmlspecialchars($row['status']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>