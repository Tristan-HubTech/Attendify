<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// Get student info
$stmt = $conn->prepare("SELECT id, name, section, address, student_id, email FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: profile.php");
    exit();
}

$student_id = $student['id'];

// Profile picture upload
if (isset($_POST['upload_pic']) && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    if ($file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];
        if (in_array($ext, $allowed)) {
            $newName = "profile_" . $student_id . "." . $ext;
            $path = "uploads/" . $newName;
            if (!is_dir("uploads")) mkdir("uploads", 0777, true);
            move_uploaded_file($file['tmp_name'], $path);
            $stmt = $conn->prepare("UPDATE students SET profile_pic=? WHERE id=?");
            $stmt->bind_param("si", $path, $student_id);
            $stmt->execute();
        }
    }
}

// fetch profile picture
$picRes = $conn->prepare("SELECT profile_pic FROM students WHERE id=?");
$picRes->bind_param("i", $student_id);
$picRes->execute();
$pic = $picRes->get_result()->fetch_assoc();
$profile_pic = $pic && $pic['profile_pic'] ? $pic['profile_pic'] : "../default-profile.png";

// Attendance summary
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
$q = $conn->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ? GROUP BY status");
$q->bind_param("i", $student_id);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $summary[$row['status']] = $row['count'];
}
$q->close();

// Attendance list
$list = $conn->prepare("
    SELECT a.date, s.name AS subject_name, a.status 
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC LIMIT 20
");
$list->bind_param("i", $student_id);
$list->execute();
$records = $list->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ACLC Student Dashboard</title>

<style>
:root {
  --royal-blue: #1D4ED8;
  --white: #FFFFFF;
  --gray: #F4F5F7;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: var(--gray);
  color: #000;
  overflow-x: hidden;
}

/* Overlay */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  display: none;
  z-index: 1999;
}
.overlay.show { display: block; }

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: -270px;
  width: 270px;
  height: 100%;
  background: var(--white);
  color: #111;
  transition: left 0.35s ease;
  padding-top: 60px;
  z-index: 2000;
  overflow-y: auto;
  box-shadow: 2px 0 8px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.sidebar.open { left: 0; }

.sidebar h2 {
  text-align: center;
  font-size: 20px;
  color: var(--royal-blue);
  border-bottom: 2px solid var(--royal-blue);
  padding-bottom: 8px;
  margin-bottom: 15px;
}

.sidebar img {
  display: block;
  margin: 0 auto 10px;
  border-radius: 50%;
  width: 100px;
  height: 100px;
  object-fit: cover;
  border: 3px solid var(--royal-blue);
}

.sidebar p {
  margin: 8px 18px;
  font-size: 15px;
  color: #333;
}

.sidebar form { text-align: center; }
.sidebar input[type=file] {
  width: 90%;
  margin: 8px auto;
  background: #f9f9f9;
  border-radius: 4px;
  border: 1px solid #ccc;
  padding: 4px;
}
.sidebar input[type=submit] {
  background: var(--royal-blue);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}
.sidebar input[type=submit]:hover { background: #1536a0; }

.sidebar .close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  color: var(--royal-blue);
  font-size: 24px;
  cursor: pointer;
  background: transparent;
  border: none;
}

/* Logout inside sidebar */
.logout {
  display: block;
  text-align: center;
  background: var(--royal-blue);
  color: white;
  padding: 10px 0;
  margin: 15px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s;
}
.logout:hover { background: #1536a0; }

/* Header */
header {
  background: var(--royal-blue);
  color: var(--white);
  padding: 15px 25px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 15px;
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1500;
}
.menu-toggle {
  font-size: 28px;
  cursor: pointer;
  user-select: none;
}
header img { height: 40px; }
header h1 { font-size: 20px; }

/* Main content */
.container {
  width: 90%;
  max-width: 1000px;
  margin: 100px auto 40px auto;
  background: var(--white);
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
  color: #000;
}

h1 { color: #000; margin-bottom: 10px; }
h2 {
  color: var(--royal-blue);
  border-bottom: 3px solid var(--royal-blue);
  display: inline-block;
  padding-bottom: 5px;
  margin-top: 30px;
}

.summary {
  display: flex;
  gap: 20px;
  margin-top: 20px;
  flex-wrap: wrap;
}
.card {
  flex: 1;
  min-width: 200px;
  background: var(--royal-blue);
  color: white;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  transition: 0.3s;
}
.card:hover { background: #1536a0; transform: translateY(-3px); }

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  color: #000;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
th { background: var(--royal-blue); color: white; }
tr:nth-child(even){ background: #f9f9f9; }
tr:hover { background: #eef3ff; }

footer {
  text-align: center;
  margin-top: 40px;
  padding: 15px;
  color: #000;
  font-size: 14px;
}
</style>
</head>

<body>

<div id="overlay" class="overlay" onclick="toggleSidebar(false)"></div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
  <div>
    <button class="close-btn" onclick="toggleSidebar(false)">×</button>
    <h2>My Profile</h2>
    <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="profile_pic" accept="image/*" required>
      <input type="submit" name="upload_pic" value="Upload">
    </form>
    <p><strong>Name:</strong> <?= htmlspecialchars($student['name']); ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($student['address']); ?></p>
    <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']); ?></p>
    <p><strong>Section:</strong> <?= htmlspecialchars($student['section']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email']); ?></p>
  </div>
  <a href="../logout.php" class="logout">Logout</a>
</div>

<header>
  <span class="menu-toggle" onclick="toggleSidebar(true)">☰</span>
  <img src="../ama.png" alt="ACLC Logo">
  <h1>ACLC College of Mandaue — Student Dashboard</h1>
</header>

<div id="main" class="container">
  <h1>Welcome, <?= htmlspecialchars($student['name']); ?></h1>
  <p><strong>Section:</strong> <?= htmlspecialchars($student['section']); ?></p>

  <div class="summary">
    <div class="card"><h3>Present</h3><p><?= $summary['Present']; ?></p></div>
    <div class="card"><h3>Absent</h3><p><?= $summary['Absent']; ?></p></div>
    <div class="card"><h3>Late</h3><p><?= $summary['Late']; ?></p></div>
  </div>

  <h2>Recent Attendance Records</h2>
  <table>
    <tr><th>Date</th><th>Subject</th><th>Status</th></tr>
    <?php while ($row = $records->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['date']); ?></td>
      <td><?= htmlspecialchars($row['subject_name']); ?></td>
      <td><?= htmlspecialchars($row['status']); ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<footer>
  © <?= date('Y'); ?> ACLC College of Mandaue | Student Information & Attendance System
</footer>

<script>
function toggleSidebar(open) {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  if (open) {
    sidebar.classList.add('open');
    overlay.classList.add('show');
  } else {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
  }
}
</script>

</body>
</html>
