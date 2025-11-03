<?php
session_start();
require '../db_connect.php';

// ✅ Restrict access to teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

// ✅ Fetch profile data
$stmt = $conn->prepare("SELECT * FROM teacher_profiles WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    header("Location: teacher_profile_setup.php");
    exit();
}

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $image_name = $profile['profile_image'];

    // ✅ Handle new image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = "../uploads/teachers/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $image_name = 'teacher_' . $teacher_id . '_' . time() . '.' . $ext;
            $target_path = $upload_dir . $image_name;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                // Optionally delete old image if not default
                if (!empty($profile['profile_image']) && $profile['profile_image'] !== 'default.png') {
                    @unlink($upload_dir . $profile['profile_image']);
                }
            }
        } else {
            $message = "⚠️ Only JPG, JPEG, or PNG files are allowed.";
        }
    }

    $stmt = $conn->prepare("UPDATE teacher_profiles SET full_name=?, department=?, phone=?, address=?, profile_image=? WHERE teacher_id=?");
    $stmt->bind_param("sssssi", $name, $department, $phone, $address, $image_name, $teacher_id);

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile - ACLC Teacher Dashboard</title>
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
.sidebar a:hover {
    background: #e21b23;
}
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
    padding: 30px 40px;
    overflow-y: auto;
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
h3 {
    color: #17345f;
    border-bottom: 2px solid #e21b23;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

/* FORM */
form {
    max-width: 500px;
    margin: 0 auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
input[type="text"], textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
button {
    background: #17345f;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}
button:hover {
    background: #e21b23;
}

/* IMAGE */
.profile-pic {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #17345f;
    display: block;
    margin: 0 auto 10px;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="../ama.png" alt="ACLC Logo">
    <h2>Teacher Panel</h2>
    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Edit Profile</h1>
        <div class="profile">
            <span>👋 <?= htmlspecialchars($profile['full_name']); ?></span>
            <img src="../uploads/teachers/<?= htmlspecialchars($profile['profile_image'] ?: 'default.png'); ?>" alt="Profile">
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="<?= str_contains($message, '⚠️') || str_contains($message, '❌') ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <h3>👤 Update Your Profile</h3>

        <form method="POST" enctype="multipart/form-data">
            <label for="profile_image">
                <img src="../uploads/teachers/<?= htmlspecialchars($profile['profile_image'] ?: 'default.png'); ?>" 
                     id="preview" alt="Profile Image" class="profile-pic">
            </label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none" onchange="previewImage(event)">
            <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name']); ?>" required>
            <input type="text" name="department" value="<?= htmlspecialchars($profile['department']); ?>" required>
            <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone']); ?>" required>
            <textarea name="address" rows="3" required><?= htmlspecialchars($profile['address']); ?></textarea>
            <div style="display:flex;justify-content:space-between;">
                <button type="submit">💾 Save Changes</button>
                <button type="button" onclick="window.location.href='attendance.php'">↩️ Cancel</button>
            </div>
        </form>
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
