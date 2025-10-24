<?php
include 'db.php';
$student_id = $_POST['student_id'];
$subject_name = $_POST['subject_name'];
$day = $_POST['day'];
$time = $_POST['time'];

mysqli_query($conn, "INSERT INTO subjects (student_id, subject_name, day, time) VALUES ('$student_id', '$subject_name', '$day', '$time')");
header("Location: student_dashboard.php");
?>
