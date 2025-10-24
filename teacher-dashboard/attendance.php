<?php
include '../config.php';
session_start();

// ✅ Check if teacher is logged in
if (!isset($_SESSION['teacher'])) {
    header("Location: login.php");
    exit();
}

// ✅ Sections and Students (all in-session)
$sections = [
    "Section A" => ["John Doe", "Jane Smith"],
    "Section B" => ["Michael Reyes", "Maria Santos"]
];

// ✅ Default subjects
if (!isset($_SESSION['subjects'])) {
    $_SESSION['subjects'] = ["Math", "Science", "English", "History"];
}

// ✅ Add New Subject
if (isset($_POST['add_subject'])) {
    $newSubject = trim($_POST['new_subject']);
    if ($newSubject && !in_array($newSubject, $_SESSION['subjects'])) {
        $_SESSION['subjects'][] = $newSubject;
    }
}

// ✅ Section Selection
$selectedSection = $_POST['section'] ?? "Section A";
$students = $sections[$selectedSection];

// ✅ Save Attendance
if (isset($_POST['save_attendance'])) {
    $attendanceData = [
        "date" => $_POST['date'],
        "subject" => $_POST['subject'],
        "section" => $selectedSection,
        "records" => $_POST['attendance']
    ];
    $_SESSION['attendance_records'][] = $attendanceData;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Sheet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .back-btn {
            background-color: #1d3557;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
            display: inline-block;
        }
        .back-btn:hover { background-color: #457b9d; }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
<h2>📅 Attendance Sheet</h2>

<!-- Add Subject -->
<form method="POST">
    <input type="text" name="new_subject" placeholder="Add New Subject" required>
    <button type="submit" name="add_subject">➕ Add Subject</button>
</form>
<br>

<!-- Attendance Form -->
<form method="POST">
    <label>Date: <input type="date" name="date" required></label>

    <label>Section:
        <select name="section" onchange="this.form.submit()">
            <?php foreach ($sections as $sec => $studentsList) {
                $selected = ($sec == $selectedSection) ? "selected" : "";
                echo "<option $selected>$sec</option>";
            } ?>
        </select>
    </label>

    <label>Subject:
        <select name="subject">
            <?php foreach ($_SESSION['subjects'] as $sub) echo "<option>$sub</option>"; ?>
        </select>
    </label>

    <table border="1" cellpadding="8">
        <tr>
            <th>Student Name</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
        </tr>
        <?php foreach ($students as $index => $student): ?>
        <tr>
            <td><?= $student; ?></td>
            <td><input type="radio" name="attendance[<?= $index; ?>]" value="Present" required></td>
            <td><input type="radio" name="attendance[<?= $index; ?>]" value="Absent"></td>
            <td><input type="radio" name="attendance[<?= $index; ?>]" value="Late"></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <button type="submit" name="save_attendance">✅ Save Attendance</button>
</form>

</body>
</html>
