<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_default_profile.php';

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
   ✅ ADD SUBJECT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $class_time = $_POST['class_time'] ?? null;
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if ($subject_name === '') {
        $message = "⚠️ Subject name required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, class_time, teacher_id, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ssi", $subject_name, $class_time, $teacher_id);
            $stmt->execute();
            $stmt->close();
            $message = "✅ Subject added successfully.";

            // Log addition
            log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Add Subject', 'Added subject: ' . $subject_name);
        } else {
            $message = "❌ Database error: " . $conn->error;
        }
    }
}

/* ================================
   ✅ DELETE SUBJECT
================================ */
if (isset($_GET['delete'])) {
    $subject_id = intval($_GET['delete']);

    // Fetch subject name for logging
    $fetch = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ?");
    $fetch->bind_param("i", $subject_id);
    $fetch->execute();
    $fetch->bind_result($subject_name);
    $fetch->fetch();
    $fetch->close();

    // Delete record
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $subject_id);
    if ($stmt->execute()) {
        $message = "🗑 Subject deleted successfully.";
        log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Delete Subject', 'Deleted subject: ' . $subject_name);
    } else {
        $message = "❌ Failed to delete subject.";
    }
    $stmt->close();
}

/* ================================
   ✅ FETCH SUBJECTS WITH TEACHER NAMES
================================ */
$subjects = [];
$q = "SELECT s.id, s.subject_name, s.class_time, s.teacher_id, 
             COALESCE(t.full_name, 'Unknown Teacher') AS teacher_name
      FROM subjects s
      LEFT JOIN teacher_profiles t ON s.teacher_id = t.teacher_id
      ORDER BY s.subject_name ASC";
$res = $conn->query($q);
while ($r = $res->fetch_assoc()) $subjects[] = $r;

/* ================================
   ✅ FETCH TEACHERS FOR DROPDOWN
================================ */
$teachers = $conn->query("
    SELECT u.id AS teacher_id, COALESCE(p.full_name, u.email) AS name
    FROM users u
    LEFT JOIN teacher_profiles p ON u.id = p.teacher_id
    WHERE u.role = 'teacher'
    ORDER BY name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Subjects | Attendify Admin</title>
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
form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
input, select, button {
    padding: 10px;
    margin-right: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}
button {
    background: #17345f;
    color: white;
    border: none;
    cursor: pointer;
}
button:hover {
    background: #1d4b83;
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
a.delete {
    color: white;
    background: #e21b23;
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
}
a.delete:hover {
    background: #c0181f;
}
.message {
    background: #fff3cd;
    color: #856404;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    border: 1px solid #ffeeba;
}
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
        <h1>Manage Subjects</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($admin_name); ?></span>
            <img src="../uploads/admins/default.png" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h2>➕ Add New Subject</h2>
        <form method="POST">
            <input type="text" name="subject_name" placeholder="Subject Name" required>
            <input type="time" name="class_time" required>
            <select name="teacher_id">
                <option value="0">-- Select Teacher --</option>
                <?php while ($t = $teachers->fetch_assoc()): ?>
                    <option value="<?= $t['teacher_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_subject">Add Subject</button>
        </form>

        <h2>📋 Current Subjects</h2>
        <?php if (count($subjects)): ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Subject</th><th>Class Time</th><th>Teacher</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach($subjects as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['subject_name']) ?></td>
                            <td><?= htmlspecialchars($s['class_time']) ?></td>
                            <td><?= htmlspecialchars($s['teacher_name'] ?: '—') ?></td>
                            <td><a class="delete" href="?delete=<?= $s['id'] ?>" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><i>No subjects found.</i></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
