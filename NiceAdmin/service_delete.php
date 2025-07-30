<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบรหัสคอร์สที่ต้องการลบ";
  exit;
}

$id = $_GET['id'];

// ลบข้อมูลเวลา (child) ก่อน
$sql_time = "DELETE FROM service_option WHERE service_id = '$id'";
mysqli_query($conn, $sql_time);

// ลบคอร์ส (parent)
$sql_course = "DELETE FROM service WHERE service_id = '$id'";

if (mysqli_query($conn, $sql_course)) {
    echo "<script>alert('ลบข้อมูลเรียบร้อย'); window.location='table_service.php';</script>";
} else {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
