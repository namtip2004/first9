<?php
require_once("connect_db.php");

$member_id = $_POST['member_id'];
$first = $_POST['mb_first_name'];
$last = $_POST['mb_last_name'];
$email = $_POST['mb_gmail'];
$tel = $_POST['mb_tel'];
$birth = $_POST['mb_birthday'];
$gender = $_POST['mb_gender'];
$address = $_POST['mb_address'];
$status = $_POST['mb_status'];

$sql = "UPDATE member SET 
  mb_first_name = '$first',
  mb_last_name = '$last',
  mb_gmail = '$email',
  mb_tel = '$tel',
  mb_birthday = '$birth',
  mb_gender = '$gender',
  mb_address = '$address',
  mb_status = '$status'
WHERE member_id = '$member_id'";

if (mysqli_query($conn, $sql)) {
  header("Location: tables_member.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
