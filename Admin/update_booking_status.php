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
$new_status = trim($_POST['new_status'] ?? '');

if (!$booking_id || $new_status === '') {
  exit('ข้อมูลไม่ครบ');
}

// ดึงสถานะปัจจุบัน
$stmt = $conn->prepare("SELECT status FROM booking WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) exit("ไม่พบการจอง");
$current = trim((string)$row['status']);

// อนุญาตเฉพาะ transition: pending -> confirmed -> complate
$allowed = false;
if ($current === 'pending'   && $new_status === 'confirmed') $allowed = true;
if ($current === 'confirmed' && $new_status === 'complate')  $allowed = true;

// (ถ้าอนาคตจะเปลี่ยนมาใช้คำว่า completed ก็รองรับเพิ่ม)
// if ($current === 'confirmed' && $new_status === 'completed') $allowed = true;

if (!$allowed) {
  exit("ไม่อนุญาตให้อัปเดตสถานะจาก '$current' เป็น '$new_status'");
}

// อัปเดต
$upd = $conn->prepare("UPDATE booking SET status = ? WHERE booking_id = ?");
$upd->bind_param("si", $new_status, $booking_id);
$upd->execute();
$upd->close();

header("Location: booking_detail.php?id=".$booking_id);
exit;
