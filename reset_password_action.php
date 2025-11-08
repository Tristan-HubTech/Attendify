<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🟢 Validate session
    if (
        !isset($_SESSION['password_reset_user']) ||
        !isset($_SESSION['password_reset_allowed']) ||
        $_SESSION['password_reset_allowed'] < time()
    ) {
        header("Location: unauthorized.php");
        exit();
    }

    $email = $_SESSION['password_reset_user'];
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';

    // ⚠️ Validate inputs
    if (empty($pw) || empty($pw2)) {
        echo "<script>alert('⚠️ Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    if ($pw !== $pw2) {
        echo "<script>alert('❌ Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    if (strlen($pw) < 6) {
        echo "<script>alert('⚠️ Password must be at least 6 characters long.'); window.history.back();</script>";
        exit();
    }

    // 🔒 Hash password securely
    $hash = password_hash($pw, PASSWORD_DEFAULT);

    // 🛠️ Prepare update query
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_otp = NULL, reset_expires = NULL WHERE email = ?");
    if (!$stmt) {
        // Log error for debugging
        error_log("SQL Error: " . $conn->error);
        header("Location: unauthorized.php");
        exit();
    }

    // ✅ Bind & execute
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();

    // 🧹 Cleanup
    $stmt->close();
    $conn->close();

    // ❎ Clear session reset permissions
    unset($_SESSION['password_reset_user']);
    unset($_SESSION['password_reset_allowed']);

    // ✅ Success message
    echo "<script>
        alert('✅ Password updated successfully! You can now log in.');
        window.location.href='login.php';
    </script>";
    exit();
} else {
    header("Location: unauthorized.php");
    exit();
}
?>
