<?php
session_start();
require '../db_connect.php';
require '../log_activity.php';

// Show SQL errors for debugging (remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ✅ Restrict to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = intval($_SESSION['user_id']);
$teacher_name = "Teacher"; // default fallback

// Safe defaults to avoid undefined warnings
$subject_count = 0;
$student_count = 0;
$attendance_today = 0;
$activities = [];

try {
    // Verify connection
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // ✅ Fetch teacher display name (fallback if 'name' column doesn’t exist)
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM teachers");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    if (in_array('name', $columns)) {
        $stmt = $conn->prepare("SELECT name FROM teachers WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT email FROM teachers WHERE id = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (isset($row['name']) && !empty($row['name'])) {
                $teacher_name = $row['name'];
            } elseif (isset($row['email'])) {
                $teacher_name = ucfirst(explode('@', $row['email'])[0]);
            }
        }
        $stmt->close();
    }

    /* ------------------------------
       Dashboard statistics queries
       ------------------------------ */

    // 1️⃣ Count subjects taught by this teacher
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM subjects WHERE teacher_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $subject_count = (int)$row['total'];
        $stmt->close();
    }

    // 2️⃣ Count distinct students enrolled across this teacher's subjects
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT e.student_id) AS total
        FROM enrollments e
        JOIN subjects s ON e.subject_id = s.id
        WHERE s.teacher_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $student_count = (int)$row['total'];
        $stmt->close();
    }

    // 3️⃣ Attendance recorded today for this teacher's subjects
    $today = date("Y-m-d");
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM attendance a
        JOIN subjects s ON a.subject_id = s.id
        WHERE s.teacher_id = ? AND a.date = ?
    ");
    if ($stmt) {
        $stmt->bind_param("is", $teacher_id, $today);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $attendance_today = (int)$row['total'];
        $stmt->close();
    }

    // 4️⃣ Recent activity for this teacher (last 5)
    $stmt = $conn->prepare("
        SELECT action, details, created_at
        FROM activity_log
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    if ($stmt) {
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Friendly debug output (remove in production)
    echo "<pre style='color:red; font-weight:bold;'>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard | Attendify</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 210px;
    background: #17345f;
    color: white;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 15px;
    height: 100vh;
}
.sidebar img {
    width: 55%;
    margin-bottom: 10px;
    border-radius: 8px;
}
.sidebar h2 { font-size: 16px; margin-bottom: 20px; }
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
}
.sidebar a:hover, .sidebar a.active { background: #e21b23; }
.logout {
    background: #e21b23;
    color: white;
    margin-top: auto;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    width: 80%;
}

/* Main */
.main { margin-left: 210px; flex-grow: 1; display: flex; flex-direction: column; }

/* Topbar */
.topbar {
    background: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.topbar h1 { margin: 0; color: #17345f; font-size: 20px; }

/* Dashboard Cards */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 30px;
}
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 25px;
    text-align: center;
}
.card h2 { font-size: 40px; color: #e21b23; margin: 0; }
.card p { color: #17345f; font-weight: bold; margin-top: 5px; }

/* Recent Activity */
.activity {
    background: white;
    margin: 0 30px 30px 30px;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.activity h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 6px;
}
.activity ul { list-style: none; padding: 0; margin: 0; }
.activity li { padding: 10px 0; border-bottom: 1px solid #eee; color: #333; font-size: 14px; }
.activity li:last-child { border-bottom: none; }
.activity small { display:block; color: gray; font-size: 12px; }
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
        <h1>Welcome back, <?= htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ?> 👋</h1>
    </div>

    <div class="dashboard">
        <div class="card">
            <h2><?= (int)$subject_count ?></h2>
            <p>Subjects You Teach</p>
        </div>
        <div class="card">
            <h2><?= (int)$student_count ?></h2>
            <p>Total Enrolled Students</p>
        </div>
        <div class="card">
            <h2><?= (int)$attendance_today ?></h2>
            <p>Attendances Recorded Today</p>
        </div>
    </div>

    <div class="activity">
        <h3>📋 Recent Activity</h3>
        <ul>
        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $act): ?>
                <li>
                    <strong><?= htmlspecialchars($act['action']) ?></strong> — <?= htmlspecialchars($act['details']) ?>
                    <small><?= htmlspecialchars(date("F j, Y g:i A", strtotime($act['created_at']))) ?></small>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><i>No recent activity recorded.</i></li>
        <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>
