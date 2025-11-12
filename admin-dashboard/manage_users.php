<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';
include __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../log_activity.php';
include __DIR__ . '/admin_nav.php';
// 🔒 Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Admin User";
$message = "";

// ✅ Fetch admin name (optional if you have admin_profiles)
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
   ✅ ADD NEW USER
================================ */
if (isset($_POST['add_user'])) {
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $email, $password, $role);

    if ($stmt->execute()) {
        $message = "✅ User added successfully.";
        log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Add User', 'Added new user: ' . $email);
    } else {
        $message = "❌ Failed to add user: " . $conn->error;
    }

    $stmt->close();
}

/* ================================
   ✅ UPDATE USER ROLE
================================ */
if (isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);

    if ($stmt->execute()) {
        $message = "✅ User updated successfully.";
        log_activity($conn, $_SESSION['user_id'], $_SESSION['role'], 'Update User', 'Updated user info (ID: ' . $user_id . ')');
    } else {
        $message = "❌ Failed to update user: " . $conn->error;
    }

    $stmt->close();
}

/* ================================
   ✅ DELETE USER (FIXED LOGGING)
================================ */
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // Fetch the user email before deletion
    $email = null;
    $fetch = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $fetch->bind_param("i", $user_id);
    $fetch->execute();
    $fetch->store_result();

    if ($fetch->num_rows > 0) {
        $fetch->bind_result($email);
        $fetch->fetch();
    }
    $fetch->close();

    // Delete user
    $del = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $deleted = $del->affected_rows > 0;
    $del->close();

    // Log the deletion
    if ($deleted) {
        $emailText = $email ? $email : "Unknown (ID: $user_id)";
        log_activity(
            $conn,
            $_SESSION['user_id'],
            $_SESSION['role'],
            'Delete User',
            'Deleted user: ' . $emailText
        );
        $message = "🗑️ User deleted successfully.";
    } else {
        $message = "❌ Failed to delete user or user not found.";
    }
}

/* ================================
   ✅ FETCH ALL USERS
================================ */
$result = $conn->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users | Attendify Admin</title>
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
    overflow-y: auto;
}
h2 {
    color: #17345f;
    font-size: 20px;
    border-bottom: 3px solid #e21b23;
    padding-bottom: 5px;
    display: inline-block;
}
.add-btn {
    background: #17345f;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    float: right;
}
.add-btn:hover {
    background: #1d4b83;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
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
    text-align: left;
    border-bottom: 1px solid #ddd;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
.edit-btn, .delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
}
.edit-btn {
    background: #1d4b83;
}
.delete-btn {
    background: #e21b23;
}
.delete-btn:hover {
    background: #c0181f;
}
.no-records {
    text-align: center;
    padding: 20px;
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
  <a href="assign_students.php" >🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php" >💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<!-- MAIN -->
<div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
        <h1>Manage Users</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($admin_name); ?></span>
            <img src="../uploads/admins/default.png" alt="Profile">
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <a href="add_user.php" class="add-btn">+ Add New User</a>
        <h2>👥 User List</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= ucfirst($row['role']); ?></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $row['id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_user.php?id=<?= $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-records">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
