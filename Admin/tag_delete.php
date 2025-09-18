<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบรหัสคอร์สที่ต้องการลบ";
  exit;
}

$id = $_GET['id'];

// ลบข้อมูลแท็ก (child) ด้วย
$sql_tag = "DELETE FROM tag_service WHERE tag_id = '$id'";
mysqli_query($conn, $sql_tag);

// ลบคอร์ส (parent)
$sql_course = "DELETE FROM tag WHERE tag_id = '$id'";

if (mysqli_query($conn, $sql_course)) {
    echo "<script>alert('delete completed'); window.location='table_tags.php';</script>";
} else {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
