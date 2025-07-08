<?php
require_once("connect_db.php");

$id = $_POST['course_ID'];
$name = $_POST['course_name'];
$detail = $_POST['course_detail'];
$price = $_POST['course_price'];

// อัปเดตคอร์ส
$sql = "UPDATE course SET course_name='$name', course_detail='$detail', course_price='$price' WHERE course_ID='$id'";
mysqli_query($conn, $sql);

// อัปเดตเวลาที่มีอยู่
if (!empty($_POST['existing_times'])) {
  foreach ($_POST['existing_times'] as $time_id => $value) {
    $t = mysqli_real_escape_string($conn, $value);
    mysqli_query($conn, "UPDATE time SET Time='$t' WHERE time_ID='$time_id'");
  }
}

// เพิ่มเวลาใหม่
if (!empty($_POST['new_times'])) {
  foreach ($_POST['new_times'] as $t) {
    if (!empty($t)) {
      $t = mysqli_real_escape_string($conn, $t);
      mysqli_query($conn, "INSERT INTO time(course_ID, Time) VALUES('$id', '$t')");
    }
  }
}

echo "<script>alert('บันทึกการแก้ไขแล้ว'); window.location='course_table.php';</script>";
?>
