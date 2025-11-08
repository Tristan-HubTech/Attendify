<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$msg = "";
// Create class
if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name'] ?? '');
    if ($class_name === '') $msg = "Enter class name.";
    else {
        $stmt = $conn->prepare("INSERT INTO classes (name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $class_name);
        $stmt->execute();
        $stmt->close();
        $msg = "Class created.";
    }
}

// Assign student to class
if (isset($_POST['assign_student'])) {
    $class_id = intval($_POST['class_id']);
    $student_id = intval($_POST['student_id']);
    // prevent duplicates
    $stmt = $conn->prepare("SELECT id FROM student_classes WHERE class_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $class_id, $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        $ins = $conn->prepare("INSERT INTO student_classes (class_id, student_id, created_at) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $class_id, $student_id);
        $ins->execute(); $ins->close();
        $msg = "Student assigned.";
    } else {
        $msg = "Student already in class.";
        $stmt->close();
    }
}

// Fetch classes & students
$classes_res = $conn->query("SELECT * FROM classes ORDER BY name");
$students_res = $conn->query("SELECT id, student_name FROM students ORDER BY student_name");

$classes = $classes_res->fetch_all(MYSQLI_ASSOC);
$students = $students_res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Manage Classes</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="main"><div class="content">
    <?php if ($msg): ?><div class="message"><?=htmlspecialchars($msg)?></div><?php endif; ?>

    <h3>Create Class</h3>
    <form method="post">
        <input name="class_name" placeholder="Class name" required>
        <button name="create_class">Create</button>
    </form>

    <h3>Assign Student to Class</h3>
    <form method="post">
        <select name="class_id" required>
            <?php foreach($classes as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
        </select>
        <select name="student_id" required>
            <?php foreach($students as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['student_name'])?></option><?php endforeach; ?>
        </select>
        <button name="assign_student">Assign</button>
    </form>

    <h3>Class Roster</h3>
    <?php foreach($classes as $c): 
        $id = intval($c['id']);
        $roster = $conn->query("SELECT sc.id, st.student_name FROM student_classes sc JOIN students st ON sc.student_id = st.id WHERE sc.class_id = $id");
    ?>
        <div style="margin:10px 0;padding:10px;border:1px solid #ddd;">
            <strong><?=htmlspecialchars($c['name'])?></strong>
            <ul>
            <?php while($r = $roster->fetch_assoc()): ?>
                <li><?=htmlspecialchars($r['student_name'])?> <a href="manage_classes.php?remove=<?=$r['id']?>" onclick="return confirm('Remove?')">remove</a></li>
            <?php endwhile; ?>
            </ul>
        </div>
    <?php endforeach; ?>

</div></div>
</body>
</html>
