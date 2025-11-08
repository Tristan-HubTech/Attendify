<?php
session_start();
require '../db_connect.php';

// admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['subject_name'] ?? '');
    $class_time = $_POST['class_time'] ?? null;
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if ($name === '') {
        $message = "⚠️ Subject name required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, class_time, teacher_id, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) $message = "DB error: " . $conn->error;
        else {
            $stmt->bind_param("ssi", $name, $class_time, $teacher_id);
            $stmt->execute();
            $stmt->close();
            $message = "✅ Subject added.";
        }
    }
}

// Delete subject
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $message = "🗑 Subject deleted.";
}

// Fetch subjects with teacher name (left join)
$subjects = [];
$q = "SELECT s.id, s.subject_name, s.class_time, s.teacher_id, t.full_name AS teacher_name
      FROM subjects s
      LEFT JOIN teacher_profiles t ON s.teacher_id = t.teacher_id
      ORDER BY s.subject_name ASC";
$res = $conn->query($q);
while ($r = $res->fetch_assoc()) $subjects[] = $r;

// Fetch teachers for dropdown
$teachers = $conn->query("SELECT user_id AS teacher_id, COALESCE(full_name, email) AS name
                          FROM teacher_profiles p
                          JOIN users u ON p.teacher_id = u.id
                          ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Subjects | Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'admin_nav.php'; /* optional include for sidebar/topbar */ ?>

<div class="main">
    <div class="content">
        <?php if ($message): ?><div class="message"><?=htmlspecialchars($message)?></div><?php endif; ?>

        <h3>➕ Add Subject</h3>
        <form method="POST">
            <input type="text" name="subject_name" placeholder="Subject name" required>
            <input type="time" name="class_time" required>
            <select name="teacher_id">
                <option value="0">-- Unassigned --</option>
                <?php while ($t = $teachers->fetch_assoc()): ?>
                    <option value="<?=intval($t['teacher_id'])?>"><?=htmlspecialchars($t['name'])?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_subject">Add</button>
        </form>

        <h3>📋 Current Subjects</h3>
        <?php if (count($subjects)): ?>
            <table>
                <tr><th>ID</th><th>Subject</th><th>Time</th><th>Teacher</th><th>Action</th></tr>
                <?php foreach($subjects as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['subject_name']) ?></td>
                    <td><?= htmlspecialchars($s['class_time']) ?></td>
                    <td><?= htmlspecialchars($s['teacher_name'] ?: '—') ?></td>
                    <td><a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p><i>No subjects yet.</i></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
