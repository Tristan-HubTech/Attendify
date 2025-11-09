<?php
require '../db_connect.php';
session_start();

// Only admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "
    SELECT 
        s.student_name,
        sub.subject_name,
        t.email AS teacher_email
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN subjects sub ON e.subject_id = sub.id
    JOIN users t ON sub.teacher_id = t.id
    ORDER BY sub.subject_name, s.student_name
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connections Overview</title>
    <style>
        body { font-family: Arial; margin:20px; background:#f4f6fa; }
        table { border-collapse:collapse; width:100%; background:white; }
        th, td { border:1px solid #ccc; padding:10px; text-align:left; }
        th { background:#17345f; color:white; }
    </style>
</head>
<body>
<h2>👩‍🏫 Student–Teacher–Subject Connections</h2>
<table>
<tr>
    <th>Student</th>
    <th>Subject</th>
    <th>Teacher</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['student_name']); ?></td>
    <td><?= htmlspecialchars($row['subject_name']); ?></td>
    <td><?= htmlspecialchars($row['teacher_email']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
