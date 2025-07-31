<?php
require_once("connect_db.php");

$customer_id = $_POST['customer_id'];
$name= $_POST['customer_name'];
$gender = $_POST['gender'];
$birth = $_POST['birthday'];
$email = $_POST['gmail'];
$tel = $_POST['tel'];
$status = $_POST['account_status'];

$sql = "UPDATE customer SET 
  customer_name = '$name',
  gender = '$gender',
  birthday = '$birth',
  gmail = '$email',
  tel = '$tel',
  account_status = '$status'
WHERE customer_id = '$customer_id'";

if (mysqli_query($conn, $sql)) {
  header("Location: table_customer.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "error: " . mysqli_error($conn);
}
?>
