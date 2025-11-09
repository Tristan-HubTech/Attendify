<?php
// log_activity.php
// ✅ Function to record user actions into activity_log table

if (!function_exists('log_activity')) {
    function log_activity($conn, $user_id, $role, $action, $details = '') {
        if (!$conn) {
            return false;
        }

        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_id, role, action, details, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if ($stmt) {
            $stmt->bind_param("isss", $user_id, $role, $action, $details);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
