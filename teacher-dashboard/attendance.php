<?php
session_start();
require '../db_connect.php';

// restrict access to teachers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

/* ---------- SECTION MANAGEMENT ---------- */

// fetch sections owned by this teacher
$sections = [];
$secStmt = $conn->prepare("SELECT * FROM sections WHERE teacher_id = ?");
$secStmt->bind_param("i", $teacher_id);
$secStmt->execute();
$res = $secStmt->get_result();
while ($row = $res->fetch_assoc()) {
    $sections[] = $row;
}
$secStmt->close();

// add new section
if (isset($_POST['add_section'])) {
    $name = trim($_POST['section_name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO sections (name, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $teacher_id);
        $stmt->execute();
        $stmt->close();
        header("Location: attendance.php");
        exit();
    }
}

// pick selected section
$selected_section = $_POST['section'] ?? ($sections[0]['name'] ?? '');

/* ---------- SUBJECT MANAGEMENT ---------- */

// add new subject
if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    if ($subject_name !== '') {
        $stmt = $conn->prepare("INSERT INTO subjects (name, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("si", $subject_name, $teacher_id);
        $stmt->execute();
        $stmt->close();
        header("Location: attendance.php");
        exit();
    }
}

// fetch teacher subjects
$subjects = [];
$subStmt = $conn->prepare("SELECT * FROM subjects WHERE teacher_id = ?");
$subStmt->bind_param("i", $teacher_id);
$subStmt->execute();
$subRes = $subStmt->get_result();
while ($row = $subRes->fetch_assoc()) {
    $subjects[] = $row;
}
$subStmt->close();

/* ---------- STUDENT FETCH ---------- */

// fetch only students in selected section
$students = [];
if ($selected_section !== '') {
    $stmt = $conn->prepare("SELECT * FROM students WHERE section = ?");
    $stmt->bind_param("s", $selected_section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

/* ---------- ATTENDANCE SAVE ---------- */

if (isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    $subject_id = $_POST['subject_id'];
    $section_name = $_POST['selected_section'];

    if ($date && $subject_id && isset($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $student_id => $status) {
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $student_id, $subject_id, $date, $status);
            $stmt->execute();
            $stmt->close();
        }
        echo "<script>alert('Attendance saved successfully.');</script>";
    } else {
        echo "<script>alert('Please select a subject and fill attendance.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard - Attendance</title>
<style>
body {font-family: Arial, sans-serif; background:#f8f9fa; margin:0;}
.container {width:90%; margin:30px auto; background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h1 {color:#1d3557;}
button,input[type=submit]{background:#1d3557;color:#fff;border:none;padding:8px 15px;border-radius:5px;cursor:pointer;}
button:hover,input[type=submit]:hover{background:#457b9d;}
select,input[type=text],input[type=date]{padding:5px;margin:5px 0;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #ccc;text-align:center;padding:8px;}
th{background:#457b9d;color:#fff;}
.logout{float:right;background:#e63946;}
.logout:hover{background:#d62828;}
</style>
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['email']); ?></h1>
    <a href="../logout.php" class="logout">Logout</a>

    <!-- SECTION MANAGEMENT -->
    <h2>Manage Sections</h2>
    <form method="POST">
        <input type="text" name="section_name" placeholder="Add new section" required>
        <button type="submit" name="add_section">Add Section</button>
    </form>

    <?php if (count($sections) > 0): ?>
    <form method="POST">
        <label>Select Section:</label>
        <select name="section" onchange="this.form.submit()">
            <?php foreach ($sections as $sec): ?>
                <option value="<?= htmlspecialchars($sec['name']); ?>" <?= ($sec['name']===$selected_section)?'selected':''; ?>>
                    <?= htmlspecialchars($sec['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php else: ?>
        <p><i>No sections created yet.</i></p>
    <?php endif; ?>

    <hr>

    <!-- SUBJECT MANAGEMENT -->
    <h2>Manage Subjects</h2>
    <form method="POST">
        <input type="text" name="subject_name" placeholder="Add new subject" required>
        <button type="submit" name="add_subject">Add Subject</button>
    </form>

    <?php if (count($subjects) > 0): ?>
        <ul>
            <?php foreach ($subjects as $s): ?>
                <li><?= htmlspecialchars($s['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><i>No subjects yet.</i></p>
    <?php endif; ?>

    <hr>

    <!-- ATTENDANCE RECORDING -->
    <h2>Record Attendance</h2>
    <?php if ($selected_section === '' || count($students) === 0): ?>
        <p><i>No students found for this section.</i></p>
    <?php else: ?>
    <form method="POST">
        <input type="hidden" name="selected_section" value="<?= htmlspecialchars($selected_section); ?>">
        <label>Date:</label>
        <input type="date" name="date" required>
        <label>Subject:</label>
        <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?= $sub['id']; ?>"><?= htmlspecialchars($sub['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <table>
            <tr>
                <th>Student Name</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
            </tr>
            <?php foreach ($students as $stu): ?>
            <tr>
                <td><?= htmlspecialchars($stu['name']); ?></td>
                <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Present" required></td>
                <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Absent"></td>
                <td><input type="radio" name="attendance[<?= $stu['id']; ?>]" value="Late"></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <input type="submit" name="save_attendance" value="Save Attendance">
    </form>
    <?php endif; ?>
</div>
</body>
</html>
