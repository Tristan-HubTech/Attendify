<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = "Administrator";
$admin_image = "default.png"; // fallback if no image is found

// ✅ Query from the existing users table
$stmt = $conn->prepare("SELECT email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $admin_name = $row['email']; // or full_name if you add that column later
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Attendify</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="admin-header">
  <div class="header-left">
    <h2>Welcome, <?= htmlspecialchars($admin_name) ?></h2>
  </div>
  <div class="header-right">
    <img src="../uploads/<?= htmlspecialchars($admin_image) ?>" alt="Admin" class="admin-avatar">
    <a href="../logout.php" class="logout-btn">Logout</a>
  </div>
</header>
</body>
</html>
