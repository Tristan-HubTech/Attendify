<?php
include 'db.php';
$id = $_POST['id'];
mysqli_query($conn, "DELETE FROM subjects WHERE id='$id'");
header("Location: student_dashboard.php");
?>
