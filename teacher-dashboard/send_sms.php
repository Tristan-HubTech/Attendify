<?php
require '../db_connect.php';

// ✅ Your SMS8 Front API credentials
$api_key = "ba176e34302a4e16687e4bb5d7c286d26dcfbe95";
$api_url = "https://app.sms8.io/services/sendFront.php";
$device_id = "5829"; // Device ID from your account (Tristan Mahusay)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $status = $_POST['status'] ?? '';

    // ✅ Fetch student's info and parent's phone
    $stmt = $conn->prepare("SELECT student_name, parent_phone FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $student_name = $row['student_name'];
        $parent_phone = preg_replace('/[^0-9]/', '', $row['parent_phone']); // sanitize number

        // ✅ Handle case where parent phone is missing
        if (empty($parent_phone)) {
            echo json_encode([
                "success" => false,
                "message" => "⚠️ No parent phone number for {$student_name}. SMS skipped."
            ]);
            exit();
        }

        // ✅ Choose message depending on attendance status
        switch ($status) {
            case 'Present':
                $message = "Hello, this is Attendify!\n\nWe’re happy to let you know that your child, {$student_name}, has safely arrived at school today and is marked as Present.\n\n- Joshua Neil";
                break;
            case 'Late':
                $message = "Good day! This is Attendify.\n\nOur system shows that your child, {$student_name}, arrived at school a bit late today. Please help us encourage punctuality.\n\n- Joshua Neil";
                break;
            case 'Absent':
                $message = "Hello from Attendify!\n\nWe noticed that your child, {$student_name}, is marked as Absent today. Please inform the school if this is due to illness or another reason.\n\n- Joshua Neil";
                break;
            default:
                $message = "Attendance update from Attendify regarding your child {$student_name}.";
                break;
        }

        // ✅ Send data as form fields (NOT JSON)
        $postData = [
            'key' => $api_key,
            'number' => $parent_phone,
            'message' => $message,
            'devices' => $device_id,
            'type' => 'sms'
        ];

        // ✅ Use CURL to send to SMS8
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // ✅ Return result
        if ($error) {
            echo json_encode([
                "success" => false,
                "message" => "❌ CURL Error: $error"
            ]);
        } else {
            echo json_encode([
                "success" => true,
                "message" => "✅ SMS sent successfully to {$parent_phone} ({$student_name})",
                "response" => $response
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "❌ Student not found"
        ]);
    }
}
?>
