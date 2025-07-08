<?php
require_once("connect_db.php");

$name = $_POST['course_name'];
$detail = $_POST['course_detail'];
$price = $_POST['course_price'];

// 1. Insert course
$sql = "INSERT INTO course (course_name, course_detail, course_price) 
        VALUES ('$name', '$detail', '$price')";
mysqli_query($conn, $sql);

// 2. Get inserted course_ID
$course_id = mysqli_insert_id($conn);

// 3. Insert times
if (!empty($_POST['new_times'])) {
  foreach ($_POST['new_times'] as $t) {
    if (!empty($t)) {
      $time = mysqli_real_escape_string($conn, $t);
      $sql_time = "INSERT INTO time (course_ID, Time) VALUES ('$course_id', '$time')";
      mysqli_query($conn, $sql_time);
    }
  }
}

echo "<script>alert('เพิ่มข้อมูลคอร์สเรียบร้อย'); window.location='course_table.php';</script>";
?>
