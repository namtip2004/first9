<?php
require_once("connect_db.php");
session_start();

// จำกัดสิทธิ์ (ปรับตามระบบจริงของคุณ)
if (!isset($_SESSION['staff_id'])) {
  http_response_code(403);
  exit("Forbidden");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$booking_id = (int)($_POST['booking_id'] ?? 0);
$new_status = booking_status_code($_POST['new_status'] ?? null);

if (!$booking_id || $new_status === null) {
  exit('ข้อมูลไม่ครบ');
}

// ดึงสถานะปัจจุบัน
$stmt = $conn->prepare("SELECT status FROM booking WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) exit("ไม่พบการจอง");
$current = booking_status_code($row['status']);
if ($current === null) {
  exit('ไม่สามารถระบุสถานะปัจจุบันได้');
}

// อนุญาตเฉพาะ transition: pending -> confirmed -> complate
$allowed = false;
if ($current === BOOKING_STATUS_PENDING   && $new_status === BOOKING_STATUS_CONFIRMED) $allowed = true;
if ($current === BOOKING_STATUS_CONFIRMED && $new_status === BOOKING_STATUS_COMPLATE)  $allowed = true;

if (!$allowed) {
  exit("ไม่อนุญาตให้อัปเดตสถานะจาก '" . booking_status_label($current) . "' เป็น '" . booking_status_label($new_status) . "'");
}

// อัปเดต
$upd = $conn->prepare("UPDATE booking SET status = ? WHERE booking_id = ?");
$upd->bind_param("ii", $new_status, $booking_id);
$upd->execute();
$upd->close();

header("Location: booking_detail.php?id=".$booking_id);
exit;
