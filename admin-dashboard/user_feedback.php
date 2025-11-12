<?php
session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';
include __DIR__ . '/admin_nav.php';
// 🔒 Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";

// ✅ Fetch admin name if available
$stmt = $conn->prepare("SELECT full_name FROM admin_profiles WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $admin_name = $row['full_name'];
    $stmt->close();
}

// ✅ Fetch all feedback (sorted by newest first)
$q = "SELECT f.id, f.user_id, f.role, f.subject, f.message, f.created_at, u.email 
      FROM feedback f 
      JOIN users u ON f.user_id = u.id 
      ORDER BY f.created_at DESC";
$feedbacks = $conn->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Feedback | Attendify Admin</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
    display: flex;
    height: 100vh;
}

/* Sidebar */
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

/* Main Content */
.main {
    margin-left: 210px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* Topbar */
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

/* Content */
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
    margin-bottom: 20px;
}
.message-box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.feedback-header .email {
    color: #17345f;
    font-weight: 600;
}
.feedback-header .role {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 13px;
    color: white;
}
.role-admin { background: #e21b23; }
.role-teacher { background: #1d4b83; }
.role-student { background: #2e8b57; }
.feedback-subject {
    font-weight: bold;
    color: #17345f;
    margin-bottom: 8px;
    font-size: 15px;
}
.feedback-message {
    white-space: pre-wrap;
    background: #f8f9fb;
    padding: 12px;
    border-radius: 8px;
    color: #333;
    font-size: 15px;
    line-height: 1.5;
    max-height: 220px;
    overflow-y: auto;
}
.feedback-date {
    margin-top: 8px;
    font-size: 13px;
    color: #777;
    text-align: right;
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
  <a href="assign_students.php" >🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php" >💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<div class="main">
  <div class="topbar">
    <h1>User Feedback</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($admin_name); ?></span>
      <img src="../uploads/admins/default.png" alt="Profile">
    </div>
  </div>

  <div class="content">
    <h2>💬 Feedback Messages</h2>

    <?php if ($feedbacks->num_rows > 0): ?>
      <?php while($f = $feedbacks->fetch_assoc()): ?>
        <div class="message-box">
          <div class="feedback-header">
            <span class="email"><?= htmlspecialchars($f['email']) ?></span>
            <span class="role role-<?= htmlspecialchars($f['role']) ?>">
              <?= ucfirst($f['role']) ?>
            </span>
          </div>
          <div class="feedback-subject">📝 <?= htmlspecialchars($f['subject'] ?: 'No subject') ?></div>
          <div class="feedback-message"><?= nl2br(htmlspecialchars($f['message'])) ?></div>
          <div class="feedback-date">📅 <?= htmlspecialchars($f['created_at']) ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;color:#777;">No feedback available.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
