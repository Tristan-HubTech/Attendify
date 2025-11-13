<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// ✅ Restrict to students only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

/* ✅ Step 1: Get actual student record safely */
$stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
if (!$stmt) {
    die("SQL Prepare Error (student_profiles): " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ✅ Step 2: Auto-create student record in 'students' table if missing */
if (!$student) {
    // Create a placeholder entry if no record exists
    $insert = $conn->prepare("INSERT INTO students (user_id, student_name, parent_phone, profile_image) VALUES (?, '', '', 'default.png')");
    if (!$insert) {
        die("SQL Prepare Error (insert student): " . $conn->error);
    }
    $insert->bind_param("i", $user_id);
    $insert->execute();
    $insert->close();

    // Fetch it again after creating
    $stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
    if (!$stmt) {
        die("SQL Prepare Error (students re-fetch): " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* ✅ Step 3: Determine real student ID */
$real_student_id = $student['id'];

/* ✅ Step 4: Attendance Summary */
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
$q = $conn->prepare("
    SELECT status, COUNT(*) AS count
    FROM attendance
    WHERE student_id = ?
    GROUP BY status
");
if ($q) {
    $q->bind_param("i", $real_student_id);
    $q->execute();
    $res = $q->get_result();
    while ($row = $res->fetch_assoc()) {
        $summary[$row['status']] = $row['count'];
    }
    $q->close();
}

/* ✅ Step 5: Recent Attendance */
$list = $conn->prepare("
    SELECT a.date, s.subject_name, a.status
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC
    LIMIT 10
");
if ($list) {
    $list->bind_param("i", $real_student_id);
    $list->execute();
    $records = $list->get_result();
    $list->close();
}

/* ✅ Step 6: Log dashboard visit */
log_activity($conn, $user_id, 'student', 'View Dashboard', 'Opened student dashboard');

/* ✅ Step 7: Determine profile picture */
$profile_pic = "../uploads/students/" . ($student['profile_image'] ?: "default.png");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | Attendify</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    background: #f4f6fa;
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
.sidebar .active { background: #e21b23; }
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
    color: #17345f;
    margin: 0;
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
    border: 2px solid #17345f;
    object-fit: cover;
}

/* CONTENT */
.content {
    padding: 30px;
}
h1 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
    margin-bottom: 15px;
}
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}
.card {
    flex: 1;
    background: #17345f;
    color: white;
    border-radius: 10px;
    text-align: center;
    padding: 20px;
}
.card h3 { margin-bottom: 5px; }

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
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Student Panel</h2>
    <a href="student_dashboard.php" >📊 Dashboard</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Student Dashboard</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($student['student_name'] ?: 'Student'); ?></span>
            <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <h1>📘 Attendance Summary</h1>
        <div class="cards">
            <div class="card"><h3>Present</h3><p><?= $summary['Present']; ?></p></div>
            <div class="card"><h3>Absent</h3><p><?= $summary['Absent']; ?></p></div>
            <div class="card"><h3>Late</h3><p><?= $summary['Late']; ?></p></div>
        </div>

        <h1>🗓 Recent Attendance Records</h1>
        <table>
            <tr>
                <th>Date</th>
                <th>Subject</th>
                <th>Status</th>
            </tr>
            <?php if (!empty($records) && $records->num_rows > 0): ?>
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
