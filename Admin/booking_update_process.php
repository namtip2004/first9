<?php
require_once("connect_db.php");
session_start();

// (ถ้าจะล็อกสิทธิ์เฉพาะแอดมิน)
if (!isset($_SESSION['staff_id'])) {
  http_response_code(403);
  exit("ต้องล็อกอินในฐานะแอดมิน");
}

if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit('Method Not Allowed'); }

$booking_id   = (int)($_POST['booking_id'] ?? 0);
$staff_id     = (int)($_POST['staff_id'] ?? 0);
$booking_date = $_POST['booking_date'] ?? '';
$time_start   = $_POST['start_time'] ?? '';

if (!$booking_id || !$staff_id || !$booking_date || !$time_start) {
  exit("ข้อมูลไม่ครบ");
}

// ดึง booking เดิม + นับนาทีรวมจากบริการเดิม (กันแก้บริการ)
$stmt = $conn->prepare("SELECT customer_id FROM booking WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$bk) exit("ไม่พบการจอง");

$stmt = $conn->prepare("
  SELECT SUM(o.duration) AS total_min
  FROM booking_seviceop bs
  JOIN service_option o ON o.option_id = bs.option_id
  WHERE bs.booking_id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_min = (int)($row['total_min'] ?? 0);
if ($total_min <= 0) exit("ไม่พบระยะเวลาของบริการเดิม");

// คำนวณเวลาเสร็จ
$start_ts = strtotime($booking_date . ' ' . $time_start);
if ($start_ts === false) exit("รูปแบบวัน/เวลาไม่ถูกต้อง");
$time_end = date("H:i", strtotime("+$total_min minutes", $start_ts));

// 1) เช็คว่าเวลาที่เลือก “มีใน get_available_times” (อย่างน้อยมีพนักงานคนไหนว่าง)
//    — เทียบกับกติกาเดียวกับหน้าลูกค้า
//    (เพื่อความเร็ว เราเช็คเฉพาะชนกับพนักงานที่เลือกก็ได้ แต่ถ้าต้องเป๊ะเท่าลูกค้า ให้เช็ค business hours/ช่องเวลาเหมือน service ฝั่งลูกค้า)
$open  = strtotime("08:00"); // ปรับตามจริง
$close = strtotime("18:00");
if ( (int)date("w", $start_ts) == 0 ) {
  // ตัวอย่างปิดวันอาทิตย์ (ถ้ามี)
  // exit("ร้านปิดให้บริการวันอาทิตย์");
}
if ($start_ts < strtotime($booking_date.' 08:00') || strtotime($time_end) > strtotime($booking_date.' 18:00')) {
  exit("นอกเวลาทำการ");
}
// (ถ้าต้องการบังคับช่วงเวลา 15 นาที)
if ((date('i', $start_ts) % 15) !== 0) {
  exit("กรุณาเลือกเวลาช่วง 15 นาที (เช่น 09:00, 09:15, 09:30)");
}

// 2) เช็คพนักงานที่เลือก “ไม่ชน” งานอื่น (ยกเว้นบันทึกนี้เอง)
$st = $conn->prepare("
  SELECT COUNT(*) AS c
  FROM booking
  WHERE staff_id = ?
    AND booking_date = ?
    AND booking_id <> ?
    AND (
        (time_start < ? AND time_end > ?)  -- slotคาบเกี่ยว
     OR (time_start >= ? AND time_start < ?) -- เริ่มในช่วง
     OR (time_end   >  ? AND time_end   <= ?) -- จบในช่วง
    )
");
$time_end_full = $time_end;  // 'H:i'
$st->bind_param("isissssss",
  $staff_id, $booking_date, $booking_id,
  $time_end_full, $time_start,
  $time_start,   $time_end_full,
  $time_start,   $time_end_full
);
$st->execute();
$conf = $st->get_result()->fetch_assoc()['c'] ?? 0;
$st->close();

if ($conf > 0) {
  exit("พนักงานไม่ว่างในช่วงเวลานี้");
}

// ผ่านทั้งหมด -> อัปเดตเฉพาะวัน/เวลา/พนักงาน
$upd = $conn->prepare("
  UPDATE booking
  SET staff_id = ?, booking_date = ?, time_start = ?, time_end = ?
  WHERE booking_id = ?
");
$upd->bind_param("isssi", $staff_id, $booking_date, $time_start, $time_end, $booking_id);
$upd->execute();
$upd->close();

header("Location: booking_detail.php?id=".$booking_id);
exit;
