<?php
require_once("../connect_db.php");
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo json_encode(["success" => false, "message" => "JSON ไม่ถูกต้อง: " . json_last_error_msg()]);
  exit;
}
if (!$data) {
  echo json_encode(["success" => false, "message" => "ไม่ได้รับข้อมูล"]);
  exit;
}

// ตรวจสอบข้อมูล
$customer_id = $data['customer_id'] ?? null;
$staff_id = $data['staff_id'] ?? null;
$date = $data['date'] ?? null;
$time_start = $data['time_start'] ?? null;
$duration = $data['duration'] ?? null;
$price = $data['price'] ?? null;
$service_options = $data['service_options'] ?? null;

$missing_fields = [];
if (!$customer_id) $missing_fields[] = "customer_id";
if (!$staff_id) $missing_fields[] = "staff_id";
if (!$date) $missing_fields[] = "date";
if (!$time_start) $missing_fields[] = "time_start";
if (!$duration) $missing_fields[] = "duration";
if (!$price) $missing_fields[] = "price";
if (!is_array($service_options) || empty($service_options)) $missing_fields[] = "service_options";

if (!empty($missing_fields)) {
  echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบ: " . implode(", ", $missing_fields)]);
  exit;
}

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['admin_id'])) {
  echo json_encode(["success" => false, "message" => "ต้องล็อกอินในฐานะแอดมิน"]);
  exit;
}

try {
  // ตรวจสอบ customer_id
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customer WHERE customer_id = ? AND account_status = 'active'");
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()['count'] == 0) {
    echo json_encode(["success" => false, "message" => "รหัสลูกค้าไม่ถูกต้อง"]);
    exit;
  }
  $stmt->close();

  if (!DateTime::createFromFormat('Y-m-d', $date) || !DateTime::createFromFormat('H:i', $time_start)) {
    echo json_encode(["success" => false, "message" => "รูปแบบวันที่หรือเวลาไม่ถูกต้อง"]);
    exit;
  }

  // คำนวณเวลา
  $start_datetime = new DateTime("$date $time_start");
  $end_datetime = clone $start_datetime;
  $end_datetime->modify("+$duration minutes");
  $start_datetime_str = $start_datetime->format("Y-m-d H:i:s");
  $end_datetime_str = $end_datetime->format("Y-m-d H:i:s");

  // ตรวจสอบว่า option_id มีอยู่ในตาราง
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM service_option WHERE option_id IN (" . implode(',', array_fill(0, count($service_options), '?')) . ")");
  $stmt->bind_param(str_repeat('i', count($service_options)), ...$service_options);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()['count'] != count($service_options)) {
    echo json_encode(["success" => false, "message" => "ตัวเลือกบริการไม่ถูกต้อง"]);
    exit;
  }
  $stmt->close();

  // ตรวจสอบราคาและส่วนลด
  $stmt = $conn->prepare("SELECT SUM(price) as total FROM service_option WHERE option_id IN (" . implode(',', array_fill(0, count($service_options), '?')) . ")");
  $stmt->bind_param(str_repeat('i', count($service_options)), ...$service_options);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $calculated_price = $result['total'] ?? 0;
  $stmt->close();

  if (abs($calculated_price - $price) > 0.1) {
    echo json_encode([
      "success" => false,
      "message" => "ราคาไม่ตรงกัน (Frontend: $price, Backend: $calculated_price)",
    ]);
    exit;
  }

  // ตรวจสอบความว่างของพนักงาน
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE staff_id = ? AND start_time < ? AND end_time > ?");
  $stmt->bind_param("iss", $staff_id, $end_datetime_str, $start_datetime_str);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
    echo json_encode(["success" => false, "message" => "พนักงานไม่ว่างในเวลานี้"]);
    exit;
  }
  $stmt->close();

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("INSERT INTO booking (customer_id, staff_id, start_time, end_time, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iissd", $customer_id, $staff_id, $start_datetime_str, $end_datetime_str, $price);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO booking_serviceop (booking_id, option_id) VALUES (?, ?)");
    foreach ($service_options as $option_id) {
      $stmt2->bind_param("ii", $booking_id, $option_id);
      $stmt2->execute();
    }
    $stmt2->close();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "สร้างการจองสำเร็จ", "booking_id" => $booking_id]);
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "ข้อผิดพลาดฐานข้อมูล: " . $e->getMessage()]);
  }
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}
$conn->close();
?>