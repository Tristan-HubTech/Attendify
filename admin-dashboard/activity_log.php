<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';
include __DIR__ . '/admin_nav.php';
// 🔒 Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";

// ✅ Fetch admin name if available
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

/* ================================
   ✅ CLEAR LOGS FEATURE
================================ */
if (isset($_POST['clear_logs'])) {
    $conn->query("TRUNCATE TABLE activity_log");
    log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Clear Logs', 'All activity logs cleared by admin.');
    $message = "🧹 All logs cleared successfully.";
}

/* ================================
   ✅ FETCH LOGS SAFELY
================================ */
$logs = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC");

// Prevent undefined variable or null issue
if (!$logs) {
    $logs = false;
    $error_message = "⚠️ Failed to fetch logs: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Activity Log | Attendify Admin</title>
<style>
body{margin:0;font-family:'Segoe UI',Arial,sans-serif;background:#f4f6fa;display:flex;height:100vh;}
.sidebar{width:210px;background:#17345f;color:white;height:100vh;position:fixed;display:flex;flex-direction:column;align-items:center;padding-top:15px;}
.sidebar img{width:55%;margin-bottom:10px;}
.sidebar h2{font-size:16px;margin-bottom:20px;}
.sidebar a{display:block;color:white;text-decoration:none;padding:8px 15px;width:85%;text-align:left;border-radius:5px;margin:3px 0;font-size:14px;transition:0.3s;}
.sidebar a:hover,.sidebar a.active{background:#e21b23;}
.logout{background:#e21b23;color:white;margin-top:auto;margin-bottom:20px;text-align:center;border-radius:6px;padding:8px;width:80%;font-size:14px;}
.main{margin-left:210px;flex-grow:1;display:flex;flex-direction:column;height:100vh;}
.topbar{background:white;padding:12px 25px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.topbar h1{margin:0;color:#17345f;font-size:20px;}
.profile{display:flex;align-items:center;gap:10px;}
.profile img{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #17345f;}
.content{padding:30px 25px;overflow-y:auto;}
h2{color:#17345f;font-size:20px;border-bottom:3px solid #e21b23;padding-bottom:5px;display:inline-block;margin-bottom:20px;}
.message{background:#fff3cd;color:#856404;padding:10px;border-radius:6px;margin-bottom:15px;border:1px solid #ffeeba;}
table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.1);}
thead{background:#17345f;color:white;}
th,td{padding:12px 15px;border-bottom:1px solid #ddd;text-align:left;}
tr:nth-child(even){background:#f9f9f9;}
.btn-clear{background:#e21b23;color:white;border:none;padding:8px 14px;border-radius:6px;font-size:14px;cursor:pointer;transition:0.2s;}
.btn-clear:hover{background:#c0181f;}
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
<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <h1>System Activity Log</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($admin_name); ?></span>
      <img src="../uploads/admins/default.png" alt="Profile">
    </div>
  </div>

  <div class="content">
    <h2>🧾 Recent Actions</h2>

    <?php if (!empty($message)): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="message" style="background:#f8d7da;color:#721c24;border-color:#f5c6cb;">
        <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <!-- 🧹 Clear Logs Button -->
    <form method="POST" style="margin-bottom:20px;">
      <button type="submit" name="clear_logs" class="btn-clear" onclick="return confirm('Are you sure you want to clear all logs?')">
        🧹 Clear All Logs
      </button>
    </form>

    <!-- LOGS TABLE -->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User ID</th>
          <th>Role</th>
          <th>Action</th>
          <th>Details</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($logs && $logs->num_rows > 0): ?>
          <?php while($log = $logs->fetch_assoc()): ?>
            <tr>
              <td><?= $log['id'] ?></td>
              <td><?= htmlspecialchars($log['user_id']) ?></td>
              <td><?= htmlspecialchars($log['role']) ?></td>
              <td><?= htmlspecialchars($log['action']) ?></td>
              <td><?= htmlspecialchars($log['details']) ?></td>
              <td><?= htmlspecialchars($log['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;color:#777;padding:15px;">No activity recorded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
