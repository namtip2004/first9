<?php
require_once("connect_db.php");
$id = $_GET['id'];
$course_id = $_GET['course'];

// ลบเวลา
mysqli_query($conn, "DELETE FROM time WHERE time_ID = '$id'");
echo "<script>window.location='form_update_course.php?id=$course_id';</script>";
?>
