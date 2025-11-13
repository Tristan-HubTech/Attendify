<?php
session_start();
require '../db_connect.php';
include __DIR__ . '/admin_nav.php';

// 🔒 Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch student–teacher–subject relationships
$sql = "
    SELECT 
        s.student_name,
        sub.subject_name,
        COALESCE(tp.full_name, t.email) AS teacher_name
    FROM enrollments e
    JOIN students s ON e.student_id = s.id
    JOIN subjects sub ON e.subject_id = sub.id
    JOIN users t ON sub.teacher_id = t.id
    LEFT JOIN teacher_profiles tp ON t.id = tp.teacher_id
    ORDER BY sub.subject_name, s.student_name
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Connections Overview | Attendify Admin</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
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
    width: 55%;
    margin-bottom: 10px;
}
.sidebar h2 {
    font-size: 16px;
    margin-bottom: 20px;
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
.sidebar a:hover, .sidebar a.active {
    background: #e21b23;
}
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

/* CONTENT */
.content {
    padding: 30px 25px;
    overflow-y: auto;
}
h2 {
    color: #17345f;
    font-size: 20px;
    border-bottom: 3px solid #e21b23;
    padding-bottom: 5px;
    display: inline-block;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
thead {
    background: #17345f;
    color: white;
}
th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
.no-records {
    text-align: center;
    padding: 15px;
    color: #6c757d;
    font-style: italic;
}
</style>
</head>
<body>

<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>
  <a href="admin.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_subjects.php">📘 Manage Subjects</a>
  <a href="manage_classes.php">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php">💬 Feedback</a>
  <a href="connections.php" class="active">🔗 Connections</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <h1>Student–Teacher–Subject Connections</h1>
  </div>

  <div class="content">
    <h2>🔗 Connections Overview</h2>
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Subject</th>
          <th>Teacher</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['student_name']); ?></td>
              <td><?= htmlspecialchars($row['subject_name']); ?></td>
              <td><?= htmlspecialchars($row['teacher_name']); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" class="no-records">No connections found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
