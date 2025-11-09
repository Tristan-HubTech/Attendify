<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';
include __DIR__ . '/admin_nav.php';
// 🔒 Restrict access to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Default admin name (optional display in header)
$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";

// ✅ Optional: Try to fetch real name from admin_profiles or users table
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

<style>
/* minimal CSS used across admin pages */
body { margin:0; font-family:'Segoe UI', Arial, sans-serif; background:#f4f6fa; display:flex; min-height:100vh; }
.sidebar { width:210px; background:#17345f; color:white; position:fixed; height:100vh; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar img{ width:55%; margin-bottom:10px; }
.sidebar a{ display:block; color:white; text-decoration:none; padding:8px 15px; width:85%; text-align:left; border-radius:5px; margin:3px 0; font-size:14px; transition:0.2s;}
.sidebar a:hover{ background:rgba(226,27,35,0.85); }
.sidebar a.active{ background:#e21b23; font-weight:600; }
.sidebar a.logout{ background:#e21b23; margin-top:auto;margin-bottom:20px; text-align:center; border-radius:6px; padding:8px; width:80%; }
.main { margin-left:210px; flex-grow:1; }
.topbar { background:white; padding:12px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.06); }
.topbar h1{ margin:0; color:#17345f; font-size:20px; }
.profile { display:flex; align-items:center; gap:10px; }
.profile img{ width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #17345f; }
.content { padding:30px 25px; }
</style>

<div class="sidebar">
  <img src="../ama.png" alt="Logo">
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

<div class="main">
  <div class="topbar">
    <h1>Welcome to Attendify</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($admin_name) ?></span>
      <img src="../uploads/admins/default.png" alt="Profile">
    </div>
  </div>

  <div class="content">
<!-- page content starts here; close tags in each page -->
<?php
// admin_layout.php - layout includes header logic
// include header logic first
require_once __DIR__ . '/admin_header.php';

// ensure $admin_name exists
if (!isset($admin_name)) $admin_name = 'Admin User';
?>
<style>
/* minimal CSS used across admin pages */
body { margin:0; font-family:'Segoe UI', Arial, sans-serif; background:#f4f6fa; display:flex; min-height:100vh; }
.sidebar { width:210px; background:#17345f; color:white; position:fixed; height:100vh; display:flex; flex-direction:column; align-items:center; padding-top:15px; }
.sidebar img{ width:55%; margin-bottom:10px; }
.sidebar a{ display:block; color:white; text-decoration:none; padding:8px 15px; width:85%; text-align:left; border-radius:5px; margin:3px 0; font-size:14px; transition:0.2s;}
.sidebar a:hover{ background:rgba(226,27,35,0.85); }
.sidebar a.active{ background:#e21b23; font-weight:600; }
.sidebar a.logout{ background:#e21b23; margin-top:auto;margin-bottom:20px; text-align:center; border-radius:6px; padding:8px; width:80%; }
.main { margin-left:210px; flex-grow:1; }
.topbar { background:white; padding:12px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.06); }
.topbar h1{ margin:0; color:#17345f; font-size:20px; }
.profile { display:flex; align-items:center; gap:10px; }
.profile img{ width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #17345f; }
.content { padding:30px 25px; }
</style>
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
  <a href="../logout.php">🚪 Logout</a>
</div>
<div class="main">
  <div class="topbar">
    <h1>Welcome to Attendify</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($admin_name) ?></span>
      <img src="../uploads/admins/default.png" alt="Profile">
    </div>
  </div>

  <div class="content">
<!-- page content starts here; close tags in each page -->
