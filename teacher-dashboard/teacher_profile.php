<?php
session_start();
require '../db_connect.php';
require '../log_activity.php'; // ✅ Connect activity logger

// ✅ Restrict access to teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

/* ---------- Fetch teacher profile ---------- */
$stmt = $conn->prepare("SELECT * FROM teacher_profiles WHERE teacher_id = ?");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    header("Location: teacher_profile_setup.php");
    exit();
}

/* ---------- Log profile view ---------- */
log_activity($conn, $teacher_id, 'teacher', 'View Profile', 'Teacher viewed their profile page.');

/* ---------- Update profile ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $image_name = $profile['profile_image'] ?? 'default.png';

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = "../uploads/teachers/";
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $image_name = 'teacher_' . $teacher_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $image_name);
            // ✅ Log new profile picture
            log_activity($conn, $teacher_id, 'teacher', 'Upload Profile Picture', "Updated profile picture ($image_name)");
        } else {
            $message = "⚠️ Only JPG, JPEG, PNG, or WEBP files are allowed.";
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE teacher_profiles 
        SET full_name=?, department=?, phone=?, address=?, profile_image=? 
        WHERE teacher_id=?");
    if (!$stmt) die("SQL Error: " . $conn->error);
    $stmt->bind_param("sssssi", $name, $department, $phone, $address, $image_name, $teacher_id);

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
        // ✅ Log profile update
        log_activity($conn, $teacher_id, 'teacher', 'Update Profile', "Updated profile information for: $name ($department)");

        // Update local array
        $profile['full_name'] = $name;
        $profile['department'] = $department;
        $profile['phone'] = $phone;
        $profile['address'] = $address;
        $profile['profile_image'] = $image_name;
    } else {
        $message = "❌ Database update failed: " . $stmt->error;
    }
    $stmt->close();
}

/* ---------- Default image fallback ---------- */
$profile_image = "../uploads/teachers/default.png";
if (!empty($profile['profile_image']) && file_exists("../uploads/teachers/" . $profile['profile_image'])) {
    $profile_image = "../uploads/teachers/" . $profile['profile_image'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ACLC Teacher Profile</title>
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
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
}
.message {
    background: #e7f3e7;
    color: #2d662d;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.error {
    background: #ffe7e7;
    color: #8b0000;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

/* FORM */
.form-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-width: 500px;
    margin: 30px auto;
    text-align: center;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left;
}
input, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    background: #17345f;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}
button:hover { background: #e21b23; }

.preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #17345f;
    object-fit: cover;
    display: block;
    margin: 10px auto 20px;
    cursor: pointer;
}
</style>
</head>
<body>

<<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="attendance.php" class="active">📊 Attendance</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="assign_students.php" >🎓 Assign Students</a>
    <a href="teacher_profile.php">👤 Profile</a>
    <a href="feedback.php">💬 Feedback</a>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>
<!-- Main content -->
<div class="main">
    <div class="topbar">
        <h1>👤 Edit Profile</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($profile['full_name']); ?></span>
            <img src="<?= htmlspecialchars($profile_image); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <label for="profile_image">
                    <img src="<?= htmlspecialchars($profile_image); ?>" id="preview" alt="Profile" class="preview">
                </label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none" onchange="previewImage(event)">

                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name']); ?>" required>

                <label>Department</label>
                <input type="text" name="department" value="<?= htmlspecialchars($profile['department']); ?>" required>

                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone']); ?>" required>

                <label>Address</label>
                <textarea name="address" required><?= htmlspecialchars($profile['address']); ?></textarea>

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
