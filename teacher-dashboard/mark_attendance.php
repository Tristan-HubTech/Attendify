<?php
session_start();
require '../db_connect.php';
require '../log_activity.php'; // ✅ include activity logger

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$subject_id = $_GET['subject_id'] ?? null;

if (!$subject_id) {
    header("Location: attendance.php");
    exit();
}

// ✅ Fetch subject info (only if owned by this teacher)
$stmt = $conn->prepare("SELECT subject_name, class_time FROM subjects WHERE id = ? AND teacher_id = ?");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("ii", $subject_id, $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
$subject = $res->fetch_assoc();
$stmt->close();

if (!$subject) {
    die("❌ Invalid subject or access denied.");
}

$subject_name = $subject['subject_name'];
$class_time = $subject['class_time'];

// ✅ Log that teacher opened this attendance page
log_activity($conn, $teacher_id, 'teacher', 'View Attendance Page', "Opened attendance page for subject: $subject_name");

/* ================================
   ✅ Fetch students linked to the subject
================================ */
$students = [];
$stmt = $conn->prepare("
    SELECT s.id, s.student_name
    FROM students s
    JOIN classes c ON s.class_id = c.id
    JOIN subjects sub ON c.id = sub.id
    WHERE sub.id = ?
");
if ($stmt) {
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
} else {
    die("SQL Error (fetch students): " . $conn->error);
}

/* ================================
   ✅ Save attendance
================================ */
$message = "";
if (isset($_POST['save_attendance'])) {
    $date = date('Y-m-d');
    $count_saved = 0;

    foreach ($_POST['attendance'] as $student_id => $status) {
        // prevent duplicates
        $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND class_id = ? AND date = ?");
        if (!$check) continue;
        $check->bind_param("iis", $student_id, $subject_id, $date);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)");
            if ($insert) {
                $insert->bind_param("iiss", $student_id, $subject_id, $date, $status);
                $insert->execute();
                $insert->close();
                $count_saved++;
            }
        }
        $check->close();
    }

    if ($count_saved > 0) {
        $message = "✅ Attendance for <b>$subject_name</b> has been saved successfully!";
        // ✅ Log to activity_log
        log_activity($conn, $teacher_id, 'teacher', 'Save Attendance', "Marked attendance for $subject_name ($count_saved students)");
    } else {
        $message = "⚠️ No new attendance records were saved (already recorded).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mark Attendance | <?= htmlspecialchars($subject_name) ?></title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
}
.container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
h2 {
    color: #17345f;
    text-align: center;
}
.message {
    background: #e7f3e7;
    color: #2d662d;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
th {
    background: #17345f;
    color: white;
}
button {
    background: #17345f;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 15px;
}
button:hover {
    background: #e21b23;
}
a.back {
    text-decoration: none;
    color: #17345f;
    font-weight: bold;
}
a.back:hover {
    color: #e21b23;
}
</style>
</head>
<body>

<div class="container">
    <a href="attendance.php" class="back">⬅ Back to Attendance Dashboard</a>
    <h2>Mark Attendance — <?= htmlspecialchars($subject_name) ?></h2>
    <p><b>Class Time:</b> <?= htmlspecialchars($class_time) ?></p>
    <p><b>Date:</b> <?= date('F j, Y') ?></p>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <table>
            <tr>
                <th>Student Name</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
            </tr>
            <?php if ($students): ?>
                <?php foreach ($students as $stu): ?>
                <tr>
                    <td><?= htmlspecialchars($stu['student_name']); ?></td>
                    <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Present" required></td>
                    <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Absent"></td>
                    <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Late"></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4"><i>No students found for this subject.</i></td></tr>
            <?php endif; ?>
        </table>
        <button type="submit" name="save_attendance">💾 Save Attendance</button>
    </form>
</div>
</body>
</html>
