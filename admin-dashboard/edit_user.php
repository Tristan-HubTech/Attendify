<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_nav.php';

// 🔒 Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch the user to edit
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_users.php");
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    if ($email !== "" && $role !== "") {
        $update = $conn->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
        $update->bind_param("ssi", $email, $role, $id);
        $update->execute();
        $update->close();

        // ✅ Log activity
        log_activity($conn, $_SESSION['user_id'], 'admin', 'Edit User', "Updated user ID: $id, email: $email, role: $role");

        header("Location: manage_users.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | Attendify Admin</title>
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
}
h2 {
    color: #17345f;
    font-size: 20px;
    border-bottom: 3px solid #e21b23;
    padding-bottom: 5px;
    display: inline-block;
}

/* FORM CARD */
.form-card {
    background: white;
    border-radius: 8px;
    padding: 25px;
    max-width: 450px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #17345f;
}
input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
button {
    background: #17345f;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #1d4b83;
}
.back-btn {
    background: #e21b23;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 6px;
    color: white;
    margin-right: 10px;
}
.back-btn:hover {
    background: #c0181f;
}
</style>
</head>
<body>

<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>

  <a href="admin.php">🏠 Dashboard</a>
  <a href="manage_users.php" class="active">👥 Manage Users</a>
  <a href="manage_subjects.php">📘 Manage Subjects</a>
  <a href="manage_classes.php">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php">💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1>Edit User</h1>
    </div>

    <div class="content">
        <h2>✏️ Update User Information</h2>
        <div class="form-card">
            <form method="POST">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

                <label>Role</label>
                <select name="role" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                </select>

                <a href="manage_users.php" class="back-btn">← Back</a>
                <button type="submit">Update User</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
