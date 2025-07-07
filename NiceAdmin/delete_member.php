<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $member_id = $_GET['id'];

    $sql="delete from member where member_ID=$member_id";

    
if (mysqli_query($conn, $sql)) {
  header("Location: tables_member.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
