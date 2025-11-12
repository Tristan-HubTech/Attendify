<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_nav.php';

// 🔒 Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";
$message = "";

// ✅ Fetch admin name
$stmt = $conn->prepare("SELECT full_name FROM admin_profiles WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $admin_name = $row['full_name'];
    $stmt->close();
}

/* =========================================
   ✅ ADD CLASS (connected with teacher)
========================================= */
if (isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $teacher_id = intval($_POST['teacher_id'] ?? 0);
    $class_time = trim($_POST['class_time']);

    if ($class_name === '') {
        $message = "⚠️ Please enter a class name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id, class_time, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $class_name, $teacher_id, $class_time);

        if ($stmt->execute()) {
            log_activity($conn, $admin_id, 'admin', 'Add Class', "Created class: $class_name");
            $message = "✅ Class added successfully!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
        $stmt->close();
    }
}

/* =========================================
   ✅ DELETE CLASS
========================================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $fetch = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
    $fetch->bind_param("i", $id);
    $fetch->execute();
    $fetch->bind_result($cname);
    $fetch->fetch();
    $fetch->close();

    // Delete related student_class connections first
    $conn->query("DELETE FROM student_classes WHERE class_id = $id");

    // Delete class
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        log_activity($conn, $admin_id, 'admin', 'Delete Class', "Deleted class: $cname");
        $message = "🗑️ Class deleted successfully.";
    }
    $stmt->close();
}

/* =========================================
   ✅ FETCH TEACHERS
========================================= */
$teachers = $conn->query("SELECT id, email FROM users WHERE role='teacher' ORDER BY email ASC");

/* =========================================
   ✅ FETCH CLASSES WITH CONNECTIONS
========================================= */
$q = "
SELECT 
    c.id, 
    c.class_name, 
    c.class_time,
    COALESCE(u.email, 'Unassigned') AS teacher_email,
    COUNT(sc.student_id) AS student_count
FROM classes c
LEFT JOIN users u ON c.teacher_id = u.id
LEFT JOIN student_classes sc ON sc.class_id = c.id
GROUP BY c.id
ORDER BY c.class_name ASC
";
$res = $conn->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Classes | Attendify Admin</title>
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
}

/* CONTENT */
.content {
    padding: 30px 25px;
}
h2 {
    color: #17345f;
    border-bottom: 3px solid #e21b23;
    padding-bottom: 5px;
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
.delete {
    color: white;
    background: #e21b23;
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
}
.delete:hover {
    background: #c0181f;
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
  <a href="manage_classes.php" class="active">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php">💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <h1>Manage Classes</h1>
    <div>👋 <?= htmlspecialchars($admin_name); ?></div>
  </div>

  <div class="content">
    <?php if ($message): ?>
      <div style="background:#fff3cd;color:#856404;padding:10px;border-radius:6px;margin-bottom:15px;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <h2>➕ Add New Class</h2>
    <form method="POST">
      <input type="text" name="class_name" placeholder="Class Name (e.g. BSIT 1A)" required>
      <input type="text" name="class_time" placeholder="Schedule (e.g. M/W 1PM-3PM)">
      <select name="teacher_id">
        <option value="0">-- Assign Teacher (optional) --</option>
        <?php while ($t = $teachers->fetch_assoc()): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['email']) ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit" name="add_class">Add Class</button>
    </form>

    <h2>🏫 Class List</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Class Name</th><th>Schedule</th><th>Teacher</th><th>Students</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($res->num_rows > 0): ?>
          <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['class_name']) ?></td>
              <td><?= htmlspecialchars($row['class_time']) ?></td>
              <td><?= htmlspecialchars($row['teacher_email']) ?></td>
              <td><?= $row['student_count'] ?></td>
              <td><a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;color:#777;">No classes found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
