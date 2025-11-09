<?php
session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";

// ✅ Optional: fetch admin name
$stmt = $conn->prepare("SELECT full_name FROM admin_profiles WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $admin_name = $row['full_name'];
    $stmt->close();
}

$message = "";

// ✅ Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['response'])) {
    $fid = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);
    $stmt = $conn->prepare("UPDATE feedback SET response = ? WHERE id = ?");
    $stmt->bind_param("si", $response, $fid);
    $stmt->execute();
    $message = "✅ Reply sent successfully!";
    $stmt->close();
}

// ✅ Fetch all feedbacks
$q = "SELECT f.id, f.user_id, f.role, f.message, f.response, f.created_at, u.email 
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
th,td{padding:12px 15px;border-bottom:1px solid #ddd;text-align:left;vertical-align:top;}
tr:nth-child(even){background:#f9f9f9;}
.role-badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:12px;color:white;}
.role-admin{background:#e21b23;}
.role-teacher{background:#1d4b83;}
.role-student{background:#2e8b57;}
.response-form textarea{width:100%;resize:vertical;padding:6px;border-radius:6px;border:1px solid #ccc;}
.response-form button{margin-top:5px;background:#17345f;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;}
.response-form button:hover{background:#1d4b83;}
.response-text{background:#f0f3fa;padding:8px;border-radius:6px;color:#17345f;}
</style>
</head>
<body>
<!-- SIDEBAR -->
<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>

  <a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">🏠 Dashboard</a>
  <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">👥 Manage Users</a>
  <a href="manage_subjects.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_subjects.php' ? 'active' : '' ?>">📘 Manage Subjects</a>
  <a href="manage_classes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_classes.php' ? 'active' : '' ?>">🏫 Manage Classes</a>
  <a href="attendance_report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'attendance_report.php' ? 'active' : '' ?>">📊 Attendance Reports</a>
  <a href="activity_log.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activity_log.php' ? 'active' : '' ?>">🕒 Activity Log</a>
  <a href="user_feedback.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_feedback.php' ? 'active' : '' ?>">💬 Feedback</a>

  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- MAIN -->
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

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Role</th>
          <th>Message</th>
          <th>Response</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($feedbacks->num_rows > 0): ?>
          <?php while($f = $feedbacks->fetch_assoc()): ?>
            <tr>
              <td><?= $f['id'] ?></td>
              <td><?= htmlspecialchars($f['email']) ?></td>
              <td>
                <span class="role-badge role-<?= htmlspecialchars($f['role']) ?>">
                  <?= ucfirst($f['role']) ?>
                </span>
              </td>
              <td><?= nl2br(htmlspecialchars($f['message'])) ?></td>
              <td>
                <?php if ($f['response']): ?>
                  <div class="response-text"><?= nl2br(htmlspecialchars($f['response'])) ?></div>
                <?php else: ?>
                  <form method="POST" class="response-form">
                    <textarea name="response" placeholder="Write a reply..." required></textarea>
                    <input type="hidden" name="feedback_id" value="<?= $f['id'] ?>">
                    <button type="submit">Send Reply</button>
                  </form>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($f['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;color:#777;padding:15px;">No feedback found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
