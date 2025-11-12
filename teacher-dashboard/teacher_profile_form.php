<?php
session_start();
require '../db_connect.php';
require '../log_activity.php'; // ✅ Connect activity log

// ✅ Only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

/* =======================================
   ✅ Check if profile already exists
======================================= */
$check = $conn->prepare("SELECT id FROM teacher_profiles WHERE teacher_id = ?");
if (!$check) die("Database error: " . $conn->error);
$check->bind_param("i", $teacher_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: teacher_profile.php");
    exit();
}
$check->close();

/* =======================================
   ✅ Handle form submission
======================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name === '' || $department === '' || $phone === '' || $address === '') {
        $message = "⚠️ Please fill out all fields.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO teacher_profiles (teacher_id, full_name, department, phone, address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            $message = "❌ Database error: " . $conn->error;
        } else {
            $stmt->bind_param("issss", $teacher_id, $name, $department, $phone, $address);
            if ($stmt->execute()) {
                // ✅ Log teacher profile creation
                log_activity($conn, $teacher_id, 'teacher', 'Profile Setup', "Teacher completed profile: $name ($department)");
                
                header("Location: teacher_profile.php");
                exit();
            } else {
                $message = "❌ Failed to save profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ✅ Log page view
log_activity($conn, $teacher_id, 'teacher', 'View Profile Setup Page', 'Accessed teacher profile setup page.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ACLC Teacher Profile Setup</title>
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
.sidebar a:hover {
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
    padding: 20px 25px;
    overflow-y: auto;
}
.message {
    background: #ffe7e7;
    color: #8b0000;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
}
.success {
    background: #e7f3e7;
    color: #2d662d;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
}

/* FORM */
form {
    max-width: 500px;
    margin: 20px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
input[type="text"], textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
button {
    background: #17345f;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}
button:hover {
    background: #e21b23;
}
</style>
</head>
<body>
<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Teacher Panel</h2>
  <a href="teacher-dashboard.php">🏠 Dashboard</a>
  <a href="attendance.php">📋 Mark Attendance</a>
  <a href="attendance_history.php">🕓 Attendance History</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="manage_students.php">👥 Manage Students</a>
  <a href="teacher_profile.php">👤 Profile</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Complete Profile</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($_SESSION['email']); ?></span>
            <img src="../uploads/teachers/default.png" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'message' : 'success' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <h3>📝 Teacher Profile Setup</h3>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="department" placeholder="Department" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <textarea name="address" placeholder="Home Address" rows="3" required></textarea>
            <button type="submit">💾 Save Profile</button>
        </form>
    </div>
</div>

</body>
</html>
