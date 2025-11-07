<?php
session_start();
require '../db_connect.php'; // ✅ correct relative path

// ✅ Allow only students
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// ✅ Fetch student details
$stmt = $conn->prepare("SELECT id, student_name, section, course, profile_image FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: profile.php");
    exit();
}

$student_id = $student['id'];

// ✅ Attendance Summary
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
$q = $conn->prepare("SELECT status, COUNT(*) AS count FROM attendance WHERE student_id = ? GROUP BY status");
$q->bind_param("i", $student_id);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $summary[$row['status']] = $row['count'];
}
$q->close();

// ✅ Recent Attendance Records
$list = $conn->prepare("
    SELECT a.date, COALESCE(s.subject_name, 'N/A') AS subject_name, a.status 
    FROM attendance a
    LEFT JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC LIMIT 20
");
$list->bind_param("i", $student_id);
$list->execute();
$records = $list->get_result();

// ✅ Profile Image
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
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
    margin: 0;
    display: flex;
    height: 100vh;
}

/* SIDEBAR */
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
    padding: 8px 15px;
    width: 85%;
    text-align: left;
    border-radius: 5px;
    margin: 3px 0;
    font-size: 14px;
    transition: 0.3s;
}
.sidebar a:hover { background: #e21b23; }
.logout {
    background: #e21b23;
    color: white;
    margin-top: auto;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    width: 80%;
    font-size: 14px;
}

/* MAIN */
.main {
    margin-left: 210px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* TOPBAR */
.topbar {
    background: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.topbar h1 {
    margin: 0;
    color: #17345f;
    font-size: 20px;
}
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #17345f;
}

/* CONTENT */
.content {
    padding: 25px;
    overflow-y: auto;
}
h2 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
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
    text-align: center;
    padding: 20px;
    border-radius: 10px;
    transition: 0.3s;
}
.card:hover { background: #e21b23; }

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    font-size: 14px;
}
th {
    background: #17345f;
    color: white;
}
tr:nth-child(even) { background: #f9f9f9; }
tr:hover { background: #eef3ff; }
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Student Panel</h2>
    <a href="student_dashboard.php">📊 Dashboard</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Attendance Overview</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($student['student_name']); ?></span>
            <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
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
</div>

</body>
</html>
                