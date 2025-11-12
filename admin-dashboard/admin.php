<?php
session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';
include __DIR__ . '/admin_nav.php';
// admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User"; // fallback name

// ✅ Fetch admin name from admin_profiles if it exists
$stmt = $conn->prepare("SELECT full_name FROM admin_profiles WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $admin_name = $row['full_name'];
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Attendify</title>
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
    border-radius: 5px;
}
.sidebar h2 {
    font-size: 16px;
    margin-bottom: 20px;
    text-align: center;
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
    padding: 30px 25px;
}
h2 {
    color: #17345f;
}
.card-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    padding: 20px;
    width: 200px;
    text-align: center;
}
.card h3 {
    color: #17345f;
    margin: 10px 0 5px;
}
</style>
</head>
<body>
<!-- SIDEBAR -->
<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>

  <a href="admin.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_subjects.php">📘 Manage Subjects</a>
  <a href="manage_classes.php">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php" >🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php" >💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>Welcome to Attendify</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($admin_name); ?></span>
            <img src="../uploads/admins/default.png" alt="Profile">
        </div>
    </div>

    <div class="content">
        <h2>📋 Dashboard Overview</h2>
        <div class="card-container">
            <div class="card">
                <h3>👨‍🏫 Teachers</h3>
                <?php
                $count = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='teacher'")->fetch_assoc()['c'];
                echo "<p>$count total</p>";
                ?>
            </div>
            <div class="card">
                <h3>🎓 Students</h3>
                <?php
                $count = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
                echo "<p>$count total</p>";
                ?>
            </div>
            <div class="card">
                <h3>📘 Subjects</h3>
                <?php
                $count = $conn->query("SELECT COUNT(*) AS c FROM subjects")->fetch_assoc()['c'];
                echo "<p>$count total</p>";
                ?>
            </div>
            <div class="card">
                <h3>📅 Attendance</h3>
                <?php
                $count = $conn->query("SELECT COUNT(*) AS c FROM attendance")->fetch_assoc()['c'];
                echo "<p>$count records</p>";
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
