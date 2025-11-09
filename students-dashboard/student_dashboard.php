<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// Only allow students
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

/* ✅ Get Student Info */
$stmt = $conn->prepare("SELECT id, student_name, section, course, profile_image FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "⚠️ No student found for this email.";
    exit();
}

$student_id = $student['id'];

/* ✅ Attendance Summary */
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];

$q = $conn->prepare("
    SELECT status, COUNT(*) AS count
    FROM attendance
    WHERE student_id = ?
    GROUP BY status
");
$q->bind_param("i", $student_id);
$q->execute();
$res = $q->get_result();

while ($row = $res->fetch_assoc()) {
    $summary[$row['status']] = $row['count'];
}
$q->close();

/* ✅ Recent Attendance Records */
$list = $conn->prepare("
    SELECT a.date, s.subject_name, a.status
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC
    LIMIT 15
");
$list->bind_param("i", $student_id);
$list->execute();
$records = $list->get_result();
$list->close();

/* ✅ Log Access */
log_activity($conn, $student_id, 'student', 'View Dashboard', 'Viewed attendance dashboard');

$profile_pic = (!empty($student['profile_image']) && file_exists("../uploads/students/" . $student['profile_image']))
    ? "../uploads/students/" . $student['profile_image']
    : "../uploads/students/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | Attendify</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6fa;
    margin: 0;
    display: flex;
}

/* Sidebar */
.sidebar {
    width: 210px;
    background: #17345f;
    color: white;
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 15px;
}
.sidebar a {
    color: white;
    text-decoration: none;
    width: 85%;
    padding: 8px;
    margin: 5px 0;
    border-radius: 5px;
    text-align: left;
}
.sidebar a:hover, .sidebar .active { background: #e21b23; }

/* Main */
.main {
    flex-grow: 1;
    padding: 25px;
}
h2 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
}
.card-container {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}
.card {
    flex: 1;
    background: #17345f;
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
th {
    background: #17345f;
    color: white;
}
tr:nth-child(even) { background: #f9f9f9; }
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="Logo" width="100">
    <a href="student_dashboard.php" class="active">📊 Dashboard</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h2>📘 Attendance Summary</h2>
    <div class="card-container">
        <div class="card"><h3>Present</h3><p><?= $summary['Present']; ?></p></div>
        <div class="card"><h3>Absent</h3><p><?= $summary['Absent']; ?></p></div>
        <div class="card"><h3>Late</h3><p><?= $summary['Late']; ?></p></div>
    </div>

    <h2>🗓 Recent Attendance Records</h2>
    <table>
        <tr>
            <th>Date</th>
            <th>Subject</th>
            <th>Status</th>
        </tr>
        <?php if ($records->num_rows > 0): ?>
            <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['date']); ?></td>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3"><i>No attendance records found.</i></td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
