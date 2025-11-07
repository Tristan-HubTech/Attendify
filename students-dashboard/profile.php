<?php
session_start();
require '../db_connect.php';

// ✅ Restrict to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$message = "";

/* ---------- Ensure students table exists ---------- */
$conn->query("
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    student_name VARCHAR(100),
    email VARCHAR(100),
    address VARCHAR(255),
    birthday DATE,
    student_code VARCHAR(50),
    section VARCHAR(100),
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

/* ---------- Fetch student ---------- */
$stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    $insert = $conn->prepare("INSERT INTO students (user_id, email) VALUES (?, ?)");
    $insert->bind_param("is", $student_id, $email);
    $insert->execute();
    $insert->close();
    $student = [
        'student_name' => '',
        'email' => $email,
        'address' => '',
        'birthday' => '',
        'student_code' => '',
        'section' => '',
        'profile_image' => 'default.png'
    ];
}

/* ---------- Handle Update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['student_name']);
    $address = trim($_POST['address']);
    $birthday = $_POST['birthday'];
    $student_code = trim($_POST['student_code']);
    $section = trim($_POST['section']);
    $image_name = $student['profile_image'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = "../uploads/students/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image_name = 'student_' . $student_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $image_name);
        } else {
            $message = "⚠️ Invalid image format. Use JPG, JPEG, or PNG.";
        }
    }

    $stmt = $conn->prepare("
        UPDATE students 
        SET student_name=?, address=?, birthday=?, student_code=?, section=?, profile_image=? 
        WHERE user_id=?
    ");
    $stmt->bind_param("ssssssi", $name, $address, $birthday, $student_code, $section, $image_name, $student_id);
    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
        $student = array_merge($student, [
            'student_name' => $name,
            'address' => $address,
            'birthday' => $birthday,
            'student_code' => $student_code,
            'section' => $section,
            'profile_image' => $image_name
        ]);
    } else {
        $message = "❌ Update failed: " . $stmt->error;
    }
    $stmt->close();
}

$profile_pic = "../uploads/students/" . ($student['profile_image'] ?: "default.png");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile | Attendify</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
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
    width: 60%;
    margin-bottom: 10px;
}
.sidebar h2 {
    font-size: 16px;
    margin-bottom: 15px;
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
    color: #17345f;
    margin: 0;
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
    border: 2px solid #17345f;
    object-fit: cover;
}

/* CONTENT */
.content {
    padding: 30px;
    display: flex;
    justify-content: center;
}
.form-card {
    background: white;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 450px;
}
.form-card h2 {
    text-align: center;
    color: #17345f;
    margin-bottom: 15px;
}
img.preview {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto 15px;
    border: 2px solid #17345f;
    cursor: pointer;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
button {
    background: #17345f;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
}
button:hover { background: #e21b23; }
.message {
    text-align: center;
    font-weight: bold;
    color: #e21b23;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Student Panel</h2>
    <a href="student_dashboard.php">📊 Dashboard</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Profile Settings</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($student['student_name'] ?: 'Student'); ?></span>
            <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <div class="form-card">
            <h2>👨‍🎓 Student Profile</h2>
            <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <label for="profile_image">
                    <img src="<?= htmlspecialchars($profile_pic); ?>" id="preview" class="preview" alt="Profile">
                </label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none" onchange="previewImage(event)">
                <input type="text" name="student_name" placeholder="Full Name" value="<?= htmlspecialchars($student['student_name']); ?>" required>
                <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($student['address']); ?>">
                <input type="date" name="birthday" value="<?= htmlspecialchars($student['birthday']); ?>">
                <input type="text" name="student_code" placeholder="Student ID" value="<?= htmlspecialchars($student['student_code']); ?>">
                <input type="text" name="section" placeholder="Section" value="<?= htmlspecialchars($student['section']); ?>">
                <button type="submit">💾 Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('preview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
