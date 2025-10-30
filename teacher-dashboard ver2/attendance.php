<?php
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['teacher'])) {
    header("Location: login.php");
    exit();
}

// ✅ Initialize sections if not exist
if (!isset($_SESSION['sections'])) {
    $_SESSION['sections'] = [
        "Section A" => [
            ["name"=>"John Doe", "age"=>16, "email"=>"john@example.com", "comments"=>[], "badges"=>[]],
            ["name"=>"Jane Smith", "age"=>15, "email"=>"jane@example.com", "comments"=>[], "badges"=>[]]
        ],
        "Section B" => [
            ["name"=>"Michael Reyes", "age"=>16, "email"=>"michael@example.com", "comments"=>[], "badges"=>[]],
            ["name"=>"Maria Santos", "age"=>15, "email"=>"maria@example.com", "comments"=>[], "badges"=>[]]
        ]
    ];
}

// ✅ Default subjects
if (!isset($_SESSION['subjects'])) $_SESSION['subjects'] = ["Math", "Science", "English", "History"];
if (!isset($_SESSION['attendance_records'])) $_SESSION['attendance_records'] = [];
if (!isset($_SESSION['feed'])) $_SESSION['feed'] = [];

// ✅ Add New Subject
if (isset($_POST['add_subject'])) {
    $newSubject = trim($_POST['new_subject']);
    if ($newSubject && !in_array($newSubject, $_SESSION['subjects'])) {
        $_SESSION['subjects'][] = $newSubject;
        $_SESSION['feed'][] = "New subject '$newSubject' added.";
    }
}

// ✅ Remove Subject
if (isset($_POST['remove_subject'])) {
    $subjectToRemove = $_POST['subject_name'];
    if (($key = array_search($subjectToRemove, $_SESSION['subjects'])) !== false) {
        unset($_SESSION['subjects'][$key]);
        $_SESSION['subjects'] = array_values($_SESSION['subjects']); // reindex
        $_SESSION['feed'][] = "Subject '$subjectToRemove' removed.";
    }
}

// ✅ Add New Section
if (isset($_POST['add_section'])) {
    $newSection = trim($_POST['new_section']);
    if ($newSection && !isset($_SESSION['sections'][$newSection])) {
        $_SESSION['sections'][$newSection] = [];
        $_SESSION['feed'][] = "New section '$newSection' created.";
    }
}

// ✅ Remove Section
if (isset($_POST['remove_section'])) {
    $sectionToRemove = $_POST['section_name'];
    if (isset($_SESSION['sections'][$sectionToRemove])) {
        unset($_SESSION['sections'][$sectionToRemove]);
        $_SESSION['feed'][] = "Section '$sectionToRemove' removed.";
        $selectedSection = array_key_first($_SESSION['sections']);
    }
}

// ✅ Section Selection
$selectedSection = $_POST['section'] ?? array_key_first($_SESSION['sections']);
$students = $_SESSION['sections'][$selectedSection] ?? [];

// ✅ Save Attendance
if (isset($_POST['save_attendance'])) {
    $attendanceData = [
        "date" => $_POST['date'],
        "subject" => $_POST['subject'],
        "section" => $selectedSection,
        "records" => $_POST['attendance'] ?? []
    ];
    $_SESSION['attendance_records'][] = $attendanceData;

    foreach ($students as $i => $stu) {
        $status = $_POST['attendance'][$i] ?? "N/A";
        $_SESSION['feed'][] = "{$stu['name']} was marked as $status on {$attendanceData['date']}.";
    }
}

// ✅ Add/Edit/Delete Comment
if (isset($_POST['add_comment'])) {
    $index = $_POST['student_index'];
    $comment = trim($_POST['comment']);
    if ($comment) {
        $students[$index]['comments'][] = $comment;
        $_SESSION['feed'][] = "Comment added for {$students[$index]['name']}: '$comment'";
    }
}
if (isset($_POST['edit_comment'])) {
    $index = $_POST['student_index'];
    $cIndex = $_POST['comment_index'];
    $newComment = trim($_POST['comment']);
    if ($newComment !== "") {
        $students[$index]['comments'][$cIndex] = $newComment;
        $_SESSION['feed'][] = "Comment updated for {$students[$index]['name']}.";
    }
}
if (isset($_POST['delete_comment'])) {
    $index = $_POST['student_index'];
    $cIndex = $_POST['comment_index'];
    if (isset($students[$index]['comments'][$cIndex])) {
        $deleted = $students[$index]['comments'][$cIndex];
        array_splice($students[$index]['comments'], $cIndex, 1);
        $_SESSION['feed'][] = "Comment deleted for {$students[$index]['name']}: '$deleted'";
    }
}

// ✅ Add Badge
if (isset($_POST['add_badge'])) {
    $index = $_POST['student_index'];
    $badge = trim($_POST['badge']);
    if ($badge) {
        $students[$index]['badges'][] = $badge;
        $_SESSION['feed'][] = "{$students[$index]['name']} received a badge: '$badge'";
    }
}

// ✅ Persist updated student data back to session
$_SESSION['sections'][$selectedSection] = $students;

// ✅ Export Attendance (as JSON)
if (isset($_POST['export_attendance'])) {
    $data = json_encode($_SESSION['attendance_records'], JSON_PRETTY_PRINT);
    header('Content-Disposition: attachment; filename="attendance_records.json"');
    header('Content-Type: application/json');
    echo $data;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<style>
body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin:0; padding:0;}
.dashboard { width:90%; margin:30px auto; background:white; border-radius:10px; padding:30px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
h1 { color:#333; }
.logout-btn { background-color:#e63946; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; float:right; margin-top:-40px; transition:0.3s;}
.logout-btn:hover { background-color:#d62828; }
table { border-collapse:collapse; width:100%; margin-top:10px;}
th, td { border:1px solid #ccc; padding:8px; text-align:center;}
th { background:#457b9d; color:white;}
input[type="text"], input[type="date"], select { padding:5px; margin:5px 0;}
button { margin-top:10px; padding:8px 15px; border-radius:5px; border:none; background:#1d3557; color:white; cursor:pointer;}
button:hover { background:#457b9d;}
hr { margin:30px 0;}
.student-name { cursor:pointer; color:#1d3557; position:relative;}
.student-name:hover::after { content:attr(data-tooltip); position:absolute; top:20px; left:0; background:#457b9d; color:white; padding:5px 10px; border-radius:5px; white-space:nowrap; z-index:10;}
.modal { display:none; position:fixed; z-index:100; padding-top:100px; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); }
.modal-content { background-color:#fefefe; margin:auto; padding:20px; border-radius:10px; width:50%; position:relative; }
.close { color:#aaa; position:absolute; top:10px; right:20px; font-size:28px; font-weight:bold; cursor:pointer; }
.close:hover { color:black; }
.feed { background:#f1faee; padding:10px; margin-top:20px; border-radius:5px; max-height:150px; overflow-y:auto; }
.toggle-btn { padding:8px 15px; margin-bottom:5px; border:none; border-radius:5px; background:#1d3557; color:white; cursor:pointer; }
.toggle-btn:hover { background:#457b9d; }
.records table { margin-top:5px; }
.comment form { display:inline; margin-left:5px; }
.comment input[type="text"] { width:200px; }
</style>
</head>
<body>

<div class="dashboard">
<h1>Welcome, <?= htmlspecialchars($_SESSION['teacher']); ?>!</h1>
<a href="logout.php" class="logout-btn">Logout</a>

<h2>🗂 Manage Sections</h2>
<form method="POST">
    <input type="text" name="new_section" placeholder="Add New Section" required>
    <button type="submit" name="add_section">➕ Create Section</button>
</form>
<ul>
<?php foreach ($_SESSION['sections'] as $sec => $stuList): ?>
    <li>
        <?= htmlspecialchars($sec) ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="section_name" value="<?= htmlspecialchars($sec) ?>">
            <button type="submit" name="remove_section">🗑️ Remove</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>

<form method="POST">
    <label>Select Section:
        <select name="section" onchange="this.form.submit()">
            <?php foreach ($_SESSION['sections'] as $sec => $stuList):
                $selected = ($sec == $selectedSection) ? "selected" : "";
            ?>
                <option <?= $selected ?>><?= htmlspecialchars($sec) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<h2>📚 Manage Subjects</h2>
<form method="POST">
    <input type="text" name="new_subject" placeholder="Add New Subject" required>
    <button type="submit" name="add_subject">➕ Add Subject</button>
</form>
<ul>
<?php foreach ($_SESSION['subjects'] as $sub): ?>
    <li>
        <?= htmlspecialchars($sub) ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="subject_name" value="<?= htmlspecialchars($sub) ?>">
            <button type="submit" name="remove_subject">🗑️ Remove</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>

<h2>📊 Attendance Management</h2>
<form method="POST">
    <label>Date: <input type="date" name="date" required></label>
    <label>Subject:
        <select name="subject">
            <?php foreach ($_SESSION['subjects'] as $sub): ?>
                <option><?= $sub ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <table>
        <tr>
            <th>Student Name</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Badges</th>
        </tr>
        <?php foreach ($students as $index => $student): ?>
            <tr>
                <td class="student-name" data-tooltip="Click for full profile" onclick="showProfile('<?= $index ?>')"><?= htmlspecialchars($student['name']); ?></td>
                <td><input type="radio" name="attendance[<?= $index ?>]" value="Present" required></td>
                <td><input type="radio" name="attendance[<?= $index ?>]" value="Absent"></td>
                <td><input type="radio" name="attendance[<?= $index ?>]" value="Late"></td>
                <td>
                    <?php foreach($student['badges'] as $b): ?>
                        🏅 <?= htmlspecialchars($b) ?><br>
                    <?php endforeach; ?>
                    <form method="POST" style="margin-top:5px;">
                        <input type="hidden" name="student_index" value="<?= $index ?>">
                        <input type="text" name="badge" placeholder="Add Badge">
                        <button type="submit" name="add_badge">🏆</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <button type="submit" name="save_attendance">✅ Save Attendance</button>
    <button type="submit" name="export_attendance">⬇ Export Records</button>
</form>

<hr>
<h3>📢 Activity Feed</h3>
<div class="feed">
<?php foreach($_SESSION['feed'] as $f): ?>
<?= htmlspecialchars($f) ?><br>
<?php endforeach; ?>
</div>
</div>

<!-- Student Modal -->
<div id="studentModal" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal()">&times;</span>
<h2 id="modalName"></h2>
<p><b>Age:</b> <span id="modalAge"></span></p>
<p><b>Email:</b> <span id="modalEmail"></span></p>
<h3>Comments</h3>
<div id="modalComments"></div>
<form method="POST">
<input type="hidden" id="studentIndex" name="student_index">
<input type="text" name="comment" placeholder="Add Comment">
<button type="submit" name="add_comment">💬</button>
</form>
</div>
</div>

<script>
const studentsData = <?= json_encode($students); ?>;
function showProfile(index) {
    const student = studentsData[index];
    document.getElementById('modalName').textContent = student.name;
    document.getElementById('modalAge').textContent = student.age;
    document.getElementById('modalEmail').textContent = student.email;
    document.getElementById('studentIndex').value = index;

    let commentsHTML = "";
    student.comments.forEach((c,i)=>{
        commentsHTML += `
        <div class="comment">
            💬 ${c}
            <form method="POST">
                <input type="hidden" name="student_index" value="${index}">
                <input type="hidden" name="comment_index" value="${i}">
                <input type="text" name="comment" value="${c}">
                <button type="submit" name="edit_comment">✏️</button>
                <button type="submit" name="delete_comment">🗑️</button>
            </form>
        </div>`;
    });
    document.getElementById('modalComments').innerHTML = commentsHTML;
    document.getElementById('studentModal').style.display = "block";
}

function closeModal() { document.getElementById('studentModal').style.display = "none"; }
window.onclick = function(event) {
    if (event.target === document.getElementById('studentModal')) closeModal();
}
</script>

</body>
</html>
