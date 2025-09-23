<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
  echo "ไม่พบข้อมูลการจอง";
  exit;
}

$cancelStatus = BOOKING_STATUS_CANCELLED;
$stmt = $conn->prepare("UPDATE booking SET status = ? WHERE booking_id = ?");
$stmt->bind_param("ii", $cancelStatus, $booking_id);

if ($stmt->execute()) {
  header("Location: table_booking.php"); // กลับไปหน้ารายชื่อสมาชิก
  exit;
} else {
  echo "เกิดข้อผิดพลาด: " . $conn->error;
}
?>
