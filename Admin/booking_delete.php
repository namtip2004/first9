<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $booking_id = $_GET['id'];

    $sql = "UPDATE booking SET 
            status = 'cancle'
        WHERE booking_id = $booking_id";

    
if (mysqli_query($conn, $sql)) {
  header("Location: table_booking.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
