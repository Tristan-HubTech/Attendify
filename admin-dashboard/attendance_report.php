<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$date = $_GET['date'] ?? date('Y-m-d');
$class_id = intval($_GET['class_id'] ?? 0);

// basic query: join attendance -> students -> subjects/classes
$query = "
SELECT a.date, s.student_name, sub.subject_name, a.status
FROM attendance a
JOIN students s ON a.student_id = s.id
LEFT JOIN subjects sub ON a.class_id = sub.id
WHERE a.date = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Attendance Report</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="main"><div class="content">
    <h3>Attendance Report for <?=htmlspecialchars($date)?></h3>
    <table>
        <tr><th>Date</th><th>Student</th><th>Subject/Class</th><th>Status</th></tr>
        <?php while($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($r['date'])?></td>
                <td><?=htmlspecialchars($r['student_name'])?></td>
                <td><?=htmlspecialchars($r['subject_name'] ?: '—')?></td>
                <td><?=htmlspecialchars($r['status'])?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div></div>
</body></html>
