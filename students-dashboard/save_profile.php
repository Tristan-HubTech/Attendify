<?php
include 'db.php';

$student_id = $_POST['student_id'];
$name = $_POST['name'];
$course = $_POST['course'];
$year = $_POST['year_level'];

$imgPath = "";
if (!empty($_FILES['profile_img']['name'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir);
    $imgPath = $targetDir . basename($_FILES["profile_img"]["name"]);
    move_uploaded_file($_FILES["profile_img"]["tmp_name"], $imgPath);
}

// Check if profile exists
$exists = mysqli_query($conn, "SELECT * FROM student_profile WHERE student_id='$student_id'");
if (mysqli_num_rows($exists) > 0) {
    mysqli_query($conn, "UPDATE student_profile SET name='$name', course='$course', year_level='$year', profile_img='$imgPath' WHERE student_id='$student_id'");
} else {
    mysqli_query($conn, "INSERT INTO student_profile (name, student_id, course, year_level, profile_img) VALUES ('$name','$student_id','$course','$year','$imgPath')");
}

header("Location: student_dashboard.php");
?>
