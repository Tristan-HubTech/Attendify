<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";

/* ---------- Ensure Admin Table Exists ---------- */
$conn->query("
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

/* ---------- Fetch admin profile safely ---------- */
$admin_name = 'Admin User';
$profile_image = "../uploads/admins/default.png";

$stmt = $conn->prepare("SELECT full_name, profile_image FROM admins WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin) {
        if (!empty($admin['full_name'])) $admin_name = $admin['full_name'];
        if (!empty($admin['profile_image']) && file_exists("../uploads/admins/" . $admin['profile_image'])) {
            $profile_image = "../uploads/admins/" . $admin['profile_image'];
        }
    }
}

/* ---------- Quick Stats ---------- */
$total_teachers = $conn->query("SELECT COUNT(*) AS total FROM teacher_profiles")->fetch_assoc()['total'] ?? 0;
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$total_attendance = $conn->query("SELECT COUNT(*) AS total FROM attendance")->fetch_assoc()['total'] ?? 0;
$total_feedback = $conn->query("SELECT COUNT(*) AS total FROM feedback")->fetch_assoc()['total'] ?? 0;
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
.sidebar a:hover { background: #e21b23; }
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
    padding: 20px 25px;
    overflow-y: auto;
}
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
}

/* DASHBOARD CARDS */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 25px;
}
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
    text-align: center;
    transition: 0.3s;
}
.card:hover { transform: translateY(-3px); }
.card h4 {
    color: #17345f;
    font-size: 18px;
    margin-bottom: 5px;
}
.card p {
    font-size: 22px;
    font-weight: bold;
    color: #e21b23;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Admin Panel</h2>
    <a href="admin.php">🏠 Dashboard</a>
    <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="manage_subjects.php">📚 Manage Subjects</a>
    <a href="activity_log.php">🕓 Activity Log</a>
    <a href="feedback.php">💬 Feedback</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1>🏠 Admin Dashboard</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($admin_name); ?></span>
            <img src="<?= htmlspecialchars($profile_image); ?>" alt="Admin Profile">
        </div>
    </div>

    <div class="content">
        <h3>📊 Overview</h3>
        <div class="dashboard-cards">
            <div class="card">
                <h4>Total Teachers</h4>
                <p><?= $total_teachers; ?></p>
            </div>
            <div class="card">
                <h4>Total Students</h4>
                <p><?= $total_students; ?></p>
            </div>
            <div class="card">
                <h4>Attendance Records</h4>
                <p><?= $total_attendance; ?></p>
            </div>
            <div class="card">
                <h4>Feedback Received</h4>
                <p><?= $total_feedback; ?></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
