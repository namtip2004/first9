<?php
require_once("../connect_db.php");
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
  error_log("JSON error: " . json_last_error_msg());
  echo json_encode(["success" => false, "message" => "JSON ไม่ถูกต้อง: " . json_last_error_msg()]);
  exit;
}
if (!$data) {
  error_log("No data received");
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
$promotion_id = $data['promotion_id'] ?? null;

$missing_fields = [];
if (!$customer_id) $missing_fields[] = "customer_id";
if (!$staff_id) $missing_fields[] = "staff_id";
if (!$date) $missing_fields[] = "date";
if (!$time_start) $missing_fields[] = "time_start";
if (!$duration) $missing_fields[] = "duration";
if (!$price) $missing_fields[] = "price";
if (!is_array($service_options) || empty($service_options)) $missing_fields[] = "service_options";

if (!empty($missing_fields)) {
  error_log("Missing fields: " . implode(", ", $missing_fields));
  echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบ: " . implode(", ", $missing_fields)]);
  exit;
}

try {
  // ตรวจสอบ customer_id
  error_log("Received customer_id: " . $customer_id);
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customer WHERE customer_id = ? AND account_status = 'active'");
  $stmt->bind_param("i", $customer_id);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  error_log("Customer check result: count = " . $result['count']);
  if ($result['count'] == 0) {
    echo json_encode(["success" => false, "message" => "รหัสลูกค้าไม่ถูกต้อง"]);
    exit;
  }
  $stmt->close();

  // ตรวจสอบรูปแบบวันที่และเวลา
  if (!DateTime::createFromFormat('Y-m-d', $date) || !DateTime::createFromFormat('H:i:s', $time_start)) {
    error_log("Invalid date or time format: date = $date, time_start = $time_start");
    echo json_encode(["success" => false, "message" => "รูปแบบวันที่หรือเวลาไม่ถูกต้อง"]);
    exit;
  }

  // ตรวจสอบว่า option_id มีอยู่ในตาราง
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM service_option WHERE option_id IN (" . implode(',', array_fill(0, count($service_options), '?')) . ")");
  $stmt->bind_param(str_repeat('i', count($service_options)), ...$service_options);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()['count'] != count($service_options)) {
    error_log("Invalid service options: " . json_encode($service_options));
    echo json_encode(["success" => false, "message" => "ตัวเลือกบริการไม่ถูกต้อง"]);
    exit;
  }
  $stmt->close();

  // ตรวจสอบราคาและส่วนลด
  $stmt = $conn->prepare("SELECT option_id, price, service_id FROM service_option WHERE option_id IN (" . implode(',', array_fill(0, count($service_options), '?')) . ")");
  $stmt->bind_param(str_repeat('i', count($service_options)), ...$service_options);
  $stmt->execute();
  $result = $stmt->get_result();
  $service_option_prices = [];
  $selected_service_ids = [];
  while ($row = $result->fetch_assoc()) {
    $service_option_prices[$row['option_id']] = $row['price'];
    $selected_service_ids[] = $row['service_id'];
  }
  $stmt->close();

  $calculated_price = array_sum($service_option_prices);
  $best_discount = 0;
  $discount_detail = null;

  if ($promotion_id) {
    $current_date = new DateTime();
    $current_date_str = $current_date->format("Y-m-d H:i:s");
    $stmt = $conn->prepare("
      SELECT discount, apply_to_all 
      FROM promotion 
      WHERE promotion_id = ? AND active = '1' AND pm_start_date <= ? AND pm_end_date >= ?
    ");
    $stmt->bind_param("iss", $promotion_id, $current_date_str, $current_date_str);
    $stmt->execute();
    $promotion = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($promotion) {
      $applies = false;
      if ($promotion['apply_to_all'] == 1) {
        $applies = true;
      } else {
        $stmt = $conn->prepare("SELECT service_id FROM promotion_service WHERE promotion_id = ?");
        $stmt->bind_param("i", $promotion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $promotion_service_ids = [];
        while ($row = $result->fetch_assoc()) {
          $promotion_service_ids[] = $row['service_id'];
        }
        $stmt->close();
        if (array_intersect($promotion_service_ids, $selected_service_ids)) {
          $applies = true;
        }
      }
      if ($applies) {
        $best_discount = floatval($promotion['discount']);
        $discount_detail = "ส่วนลด {$best_discount}% จากโปรโมชัน ID: {$promotion_id}";
      } else {
        error_log("Promotion $promotion_id not applicable to selected services: " . json_encode($selected_service_ids));
        echo json_encode(["success" => false, "message" => "โปรโมชันไม่สามารถใช้กับบริการที่เลือกได้"]);
        exit;
      }
    } else {
      error_log("Invalid or expired promotion: $promotion_id");
      echo json_encode(["success" => false, "message" => "โปรโมชันไม่ถูกต้องหรือหมดอายุ"]);
      exit;
    }
  }

  $total_discount = $calculated_price * ($best_discount / 100);
  $final_price = $calculated_price - $total_discount;

  if (abs($final_price - $price) > 0.1) {
    error_log("Price mismatch: Frontend = $price, Backend = $final_price, Discount = $best_discount%, Promotion ID = $promotion_id");
    echo json_encode([
      "success" => false,
      "message" => "ราคาไม่ตรงกัน (Frontend: $price, Backend: $final_price, Discount: $best_discount%, Promotion ID: $promotion_id)",
      "selected_service_ids" => $selected_service_ids
    ]);
    exit;
  }

  // คำนวณเวลา
  $start_datetime = new DateTime("$date $time_start");
  $end_datetime = clone $start_datetime;
  $end_datetime->modify("+$duration minutes");
  $start_datetime_str = $start_datetime->format("Y-m-d H:i:s");
  $end_datetime_str = $end_datetime->format("Y-m-d H:i:s");
  $booking_date = $start_datetime->format("Y-m-d");

  // ตรวจสอบความว่างของพนักงาน
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE staff_id = ? AND time_start < ? AND time_end > ?");
  $stmt->bind_param("iss", $staff_id, $end_datetime_str, $start_datetime_str);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
    error_log("Staff not available: staff_id = $staff_id, time = $start_datetime_str to $end_datetime_str");
    echo json_encode(["success" => false, "message" => "พนักงานไม่ว่างในเวลานี้"]);
    exit;
  }
  $stmt->close();

  $conn->begin_transaction();
  try {
    // Insert ลงตาราง booking
    $stmt = $conn->prepare("
      INSERT INTO booking (customer_id, staff_id, booking_date, time_start, time_end, total_price, total_discount, final_price, discount_detail, status) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iisssddds", $customer_id, $staff_id, $booking_date, $start_datetime_str, $end_datetime_str, $calculated_price, $total_discount, $final_price, $discount_detail);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();

    // Insert ลงตาราง booking_serviceop
    $stmt2 = $conn->prepare("INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($service_options as $option_id) {
      $option_price = $service_option_prices[$option_id] ?? 0;
      $option_discount = $option_price * ($best_discount / 100);
      $option_net_price = $option_price - $option_discount;
      $stmt2->bind_param("iidd", $booking_id, $option_id, $option_price, $option_discount, $option_net_price);
      $stmt2->execute();
    }
    $stmt2->close();

    $conn->commit();
    error_log("Booking created successfully: booking_id = $booking_id, customer_id = $customer_id");
    echo json_encode(["success" => true, "message" => "สร้างการจองสำเร็จ", "booking_id" => $booking_id]);
  } catch (Exception $e) {
    $conn->rollback();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "ข้อผิดพลาดฐานข้อมูล: " . $e->getMessage()]);
  }
} catch (Exception $e) {
  error_log("General error: " . $e->getMessage());
  echo json_encode(["success" => false, "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}
$conn->close();
?>