<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $customer_id = $_GET['id'];

    $sql = "UPDATE customer SET 
            account_status = 'inactive'
        WHERE customer_id = $customer_id";

    
if (mysqli_query($conn, $sql)) {
  header("Location: table_customer.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
