<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_nav.php';
include __DIR__ . '/admin_default_profile.php';

// 🔒 Restrict access to admin only
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

$message = "";

/* ================================
   ✅ CREATE NEW CLASS
================================ */
if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name'] ?? '');

    if ($class_name === '') {
        $message = "⚠️ Please enter a class name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, created_at) VALUES (?, NOW())");
        if ($stmt) {
            $stmt->bind_param("s", $class_name);
            $stmt->execute();
            $stmt->close();

            // 🔹 Log the action
            log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Create Class', "Created class: $class_name");

            $message = "✅ Class created successfully.";
        } else {
            $message = "❌ Database error: " . $conn->error;
        }
    }
}

/* ================================
   ✅ ASSIGN STUDENT TO CLASS
================================ */
if (isset($_POST['assign_student'])) {
    $class_id = intval($_POST['class_id']);
    $student_id = intval($_POST['student_id']);

    $check = $conn->prepare("SELECT id FROM student_classes WHERE class_id = ? AND student_id = ?");
    $check->bind_param("ii", $class_id, $student_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $check->close();

        $insert = $conn->prepare("INSERT INTO student_classes (class_id, student_id, created_at) VALUES (?, ?, NOW())");
        $insert->bind_param("ii", $class_id, $student_id);
        $insert->execute();
        $insert->close();

        // 🔹 Fetch student email for readable log
        $student_email = '';
        $fetch = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $fetch->bind_param("i", $student_id);
        $fetch->execute();
        $fetch->bind_result($student_email);
        $fetch->fetch();
        $fetch->close();

        // 🔹 Fetch class name for readable log
        $class_name = '';
        $fetch2 = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
        $fetch2->bind_param("i", $class_id);
        $fetch2->execute();
        $fetch2->bind_result($class_name);
        $fetch2->fetch();
        $fetch2->close();

        // ✅ Log assignment action
        log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Assign Student', "Assigned $student_email to class: $class_name");

        $message = "✅ Student assigned successfully.";
    } else {
        $message = "⚠️ This student is already assigned to this class.";
        $check->close();
    }
}

/* ================================
   ✅ REMOVE STUDENT FROM CLASS
================================ */
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);

    // Fetch class and student info for logs before deletion
    $fetch = $conn->prepare("
        SELECT u.email, c.class_name
        FROM student_classes sc
        JOIN users u ON sc.student_id = u.id
        JOIN classes c ON sc.class_id = c.id
        WHERE sc.id = ?
    ");
    $fetch->bind_param("i", $remove_id);
    $fetch->execute();
    $fetch->bind_result($email, $class_name);
    $fetch->fetch();
    $fetch->close();

    // Delete the record
    $del = $conn->prepare("DELETE FROM student_classes WHERE id = ?");
    $del->bind_param("i", $remove_id);
    $del->execute();
    $del->close();

    // ✅ Log removal action
    log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Remove Student', "Removed $email from class: $class_name");

    $message = "🗑️ Student removed from class.";
}

/* ================================
   ✅ DELETE CLASS
================================ */
if (isset($_GET['delete_class'])) {
    $class_id = intval($_GET['delete_class']);
    $class_name = '';

    // Fetch name before deletion
    $fetch = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
    $fetch->bind_param("i", $class_id);
    $fetch->execute();
    $fetch->bind_result($class_name);
    $fetch->fetch();
    $fetch->close();

    // Delete the class
    $del = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $del->bind_param("i", $class_id);
    $del->execute();
    $del->close();

    // ✅ Log deletion
    log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Delete Class', "Deleted class: $class_name");

    $message = "🗑️ Class deleted successfully.";
}

/* ================================
   ✅ FETCH CLASSES & STUDENTS
================================ */
$classes_res = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
$classes = $classes_res ? $classes_res->fetch_all(MYSQLI_ASSOC) : [];

$students_res = $conn->query("SELECT id, email FROM users WHERE role = 'student' ORDER BY email ASC");
$students = $students_res ? $students_res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Classes | Attendify Admin</title>
<style>
:root {
    --primary-color: #17345f;
    --accent-color: #e21b23;
    --background-color: #f4f6fa;
    --white: #ffffff;
}
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: var(--background-color);
    display: flex;
}
.main {
    margin-left: 210px;
    margin-top: 60px; 
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px);
}
.topbar {
    background: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 210px;
    right: 0;
    height: 60px;
    z-index: 1000;
}
.topbar h1 { color: var(--primary-color); font-size: 20px; margin: 0; }
.profile { display: flex; align-items: center; gap: 10px; font-weight: 500; }
.profile img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid var(--primary-color); object-fit: cover; }
.section {
    background: var(--white);
    border-radius: 10px;
    padding: 20px;
    margin: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.section h3 {
    color: var(--primary-color);
    border-bottom: 3px solid var(--accent-color);
    display: inline-block;
    padding-bottom: 3px;
    margin-bottom: 15px;
}
.message {
    background: #fff3cd;
    color: #856404;
    padding: 10px;
    border-radius: 6px;
    margin: 20px;
    border-left: 5px solid #ffeeba;
}
input, select, button {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}
button {
    background: var(--primary-color);
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 500;
}
button:hover { background: #1d4b83; }
.table-container { overflow-x: auto; }
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
th { background: var(--primary-color); color: white; }
tr:hover { background: #f1f3f9; }
.action-btn {
    background: var(--accent-color);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    text-decoration: none;
    font-size: 13px;
    cursor: pointer;
}
.action-btn:hover { background: #b9161d; }
</style>
</head>
<body>

<div class="main">
  <div class="topbar">
    <h1>Manage Classes</h1>
    <div class="profile">
      <span>👋 <?= htmlspecialchars($admin_name); ?></span>
      <img src="../uploads/admins/default.png" alt="Profile">
    </div>
  </div>

  <?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="section">
    <h3>➕ Add New Class</h3>
    <form method="POST">
      <input type="text" name="class_name" placeholder="Class Name" required>
      <button type="submit" name="create_class">Add Class</button>
    </form>
  </div>

  <div class="section">
    <h3>👥 Assign Student to Class</h3>
    <form method="POST">
      <select name="class_id" required>
        <option value="">Select Class</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="student_id" required>
        <option value="">Select Student</option>
        <?php foreach ($students as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['email']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" name="assign_student">Assign</button>
    </form>
  </div>

  <div class="section table-container">
    <h3>📋 Current Classes</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Class Name</th>
        <th>Students</th>
        <th>Action</th>
      </tr>
      <?php if (count($classes) > 0): ?>
        <?php foreach ($classes as $c): 
            $class_id = intval($c['id']);
            $roster = $conn->query("
              SELECT sc.id, u.email 
              FROM student_classes sc
              JOIN users u ON sc.student_id = u.id
              WHERE sc.class_id = $class_id
              ORDER BY u.email
            ");
        ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['class_name']) ?></td>
            <td>
              <?php if ($roster && $roster->num_rows > 0): ?>
                <?php while($r = $roster->fetch_assoc()): ?>
                  <?= htmlspecialchars($r['email']) ?>
                  <a href="manage_classes.php?remove=<?= $r['id'] ?>" class="action-btn" onclick="return confirm('Remove this student?')">Remove</a><br>
                <?php endwhile; ?>
              <?php else: ?>
                <i>No students assigned.</i>
              <?php endif; ?>
            </td>
            <td>
              <a href="manage_classes.php?delete_class=<?= $c['id'] ?>" class="action-btn" onclick="return confirm('Delete this class?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4"><i>No classes created yet.</i></td></tr>
      <?php endif; ?>
    </table>
  </div>
<
