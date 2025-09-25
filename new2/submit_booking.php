<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../booking_status.php';
require_once __DIR__ . '/../Admin/promotion_utils.php';

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->beginTransaction();

    // Validate inputs
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception('กรุณาเข้าสู่ระบบก่อนจอง');
    }
    $customer_id = (int)$_SESSION['customer_id'];

    if (!isset($_POST['staff_id']) || !is_numeric($_POST['staff_id'])) {
        throw new Exception('กรุณาเลือกผู้ให้บริการ');
    }
    $staff_id = (int)$_POST['staff_id'];

    if (!isset($_POST['booking_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['booking_date'])) {
        throw new Exception('วันที่ไม่ถูกต้อง');
    }
    $booking_date = $_POST['booking_date'];

    if (!isset($_POST['start_time']) || !preg_match('/^\d{2}:\d{2}$/', $_POST['start_time'])) {
        throw new Exception('เวลาเริ่มต้นไม่ถูกต้อง');
    }
    $start_time = $_POST['start_time'];

    if (!isset($_POST['services']) || !isset($_POST['options'])) {
        throw new Exception('กรุณาเลือกบริการและตัวเลือก');
    }

    // Handle evidence file upload
    $evidenceFileName = null;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../admin/assets/img/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // ใช้ชื่อไฟล์เดิม
        $originalFileName = $_FILES['evidence']['name'];
        $fileInfo = pathinfo($originalFileName);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('ประเภทไฟล์ไม่ถูกต้อง กรุณาอัพโหลดไฟล์ภาพ (jpg, png, gif)');
        }
        
        // Validate file size (max 5MB)
        if ($_FILES['evidence']['size'] > 5 * 1024 * 1024) {
            throw new Exception('ไฟล์ขนาดใหญ่เกินไป (สูงสุด 5MB)');
        }
        
        // ตรวจสอบว่าไฟล์มีชื่อซ้ำหรือไม่ ถ้าซ้ำให้เพิ่มเลขต่อท้าย
        $evidenceFileName = $originalFileName;
        $uploadPath = $uploadDir . $evidenceFileName;
        $counter = 1;
        
        while (file_exists($uploadPath)) {
            $nameWithoutExt = $fileInfo['filename'];
            $evidenceFileName = $nameWithoutExt . '_' . $counter . '.' . $extension;
            $uploadPath = $uploadDir . $evidenceFileName;
            $counter++;
        }
        
        if (!move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadPath)) {
            throw new Exception('เกิดข้อผิดพลาดในการอัพโหลดไฟล์');
        }
    } else {
        throw new Exception('กรุณาแนบหลักฐานการชำระเงิน');
    }

    // Convert comma-separated strings to arrays
    $services = array_filter(explode(',', $_POST['services']));
    $options = array_filter(explode(',', $_POST['options']));

    if (count($services) !== count($options)) {
        throw new Exception('ข้อมูลบริการและตัวเลือกไม่สอดคล้องกัน');
    }

    $services = array_values(array_map('intval', $services));
    $options = array_values(array_map('intval', $options));

    $promotionDiscounts = [];
    $promotionSummary = '';
    $promotionTotalDiscount = 0.0;
    $promotionConn = null;

    try {
        $promotionConn = new mysqli('127.0.0.1', 'root', '', 'first9');
        if ($promotionConn->connect_error) {
            throw new Exception('ไม่สามารถเชื่อมต่อข้อมูลโปรโมชั่นได้');
        }

        ensurePromotionSupport($promotionConn);
        $targetDateTime = date('Y-m-d H:i:s');
        $promotionResult = getApplicableOptionDiscounts($promotionConn, $options, $targetDateTime);
        $promotionDiscounts = $promotionResult['by_option'] ?? [];
        $promotionSummary = trim(summarizePromotionDiscountDetail($promotionResult['by_promotion'] ?? []));
        $promotionTotalDiscount = (float)($promotionResult['total_discount'] ?? 0);
    } catch (Exception $promotionException) {
        $promotionDiscounts = [];
        $promotionSummary = '';
        $promotionTotalDiscount = 0.0;
    } finally {
        if ($promotionConn instanceof mysqli) {
            $promotionConn->close();
        }
    }

    // Calculate total duration and prices
    $total_duration = 0;
    $total_price = 0.0;
    $total_discount = 0.0;
    $optionPricing = [];

    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT duration, price, service_id FROM service_option WHERE option_id = ?");
        $stmt->execute([$option_id]);
        $option = $stmt->fetch();
        if (!$option || (int)$option['service_id'] !== $services[$index]) {
            throw new Exception('ตัวเลือกหรือบริการไม่ถูกต้อง');
        }

        $duration = (int)$option['duration'];
        $price = (float)$option['price'];

        $total_duration += $duration;
        $total_price += $price;

        $discountInfo = $promotionDiscounts[$option_id] ?? null;
        $netPrice = $price;
        $discountAmount = 0.0;

        if ($discountInfo) {
            if (isset($discountInfo['final_price'])) {
                $netFromPromotion = (float)$discountInfo['final_price'];
                if ($netFromPromotion >= 0 && $netFromPromotion <= $price) {
                    $netPrice = $netFromPromotion;
                    $discountAmount = $price - $netPrice;
                }
            }

            if ($discountAmount <= 0 && isset($discountInfo['discount_amount'])) {
                $discountAmount = (float)$discountInfo['discount_amount'];
                if ($discountAmount < 0) {
                    $discountAmount = 0.0;
                }
                if ($discountAmount > $price) {
                    $discountAmount = $price;
                }
                $netPrice = $price - $discountAmount;
            }
        }

        if ($netPrice < 0) {
            $netPrice = 0.0;
        }

        $total_discount += $discountAmount;
        $optionPricing[$option_id] = [
            'price' => $price,
            'discount' => $discountAmount,
            'net' => $netPrice,
        ];
    }

    if ($promotionTotalDiscount > 0) {
        $total_discount = max($total_discount, min($promotionTotalDiscount, $total_price));
    }

    if ($total_discount > $total_price) {
        $total_discount = $total_price;
    }

    $final_price = $total_price - $total_discount;
    if ($final_price < 0) {
        $final_price = 0.0;
    }

    if ($total_duration <= 0) {
        throw new Exception('กรุณาเลือกบริการอย่างน้อยหนึ่งรายการ');
    }

    $startTimestamp = strtotime($booking_date . ' ' . $start_time);
    if ($startTimestamp === false) {
        throw new Exception('เวลาเริ่มต้นไม่ถูกต้อง');
    }

    $endTimestamp = strtotime("+$total_duration minutes", $startTimestamp);
    $end_time = date('H:i', $endTimestamp);

    $businessDay = date('l', strtotime($booking_date));
    $stmt = $pdo->prepare("SELECT open_time, close_time, is_closed FROM business_hours WHERE day_of_week = ?");
    $stmt->execute([$businessDay]);
    $businessHours = $stmt->fetch();

    if (!$businessHours) {
        throw new Exception('ยังไม่ได้ตั้งค่าเวลาเปิด-ปิดสำหรับวันที่เลือก');
    }

    if ((int)$businessHours['is_closed'] === 1) {
        throw new Exception('ร้านปิดทำการในวันที่เลือก');
    }

    $openTimestamp = strtotime($booking_date . ' ' . $businessHours['open_time']);
    $closeTimestamp = strtotime($booking_date . ' ' . $businessHours['close_time']);

    if ($openTimestamp === false || $closeTimestamp === false || $closeTimestamp <= $openTimestamp) {
        throw new Exception('การตั้งค่าเวลาเปิด-ปิดไม่ถูกต้อง');
    }

    if ($startTimestamp < $openTimestamp || $endTimestamp > $closeTimestamp) {
        throw new Exception('เวลาจองอยู่นอกเวลาทำการ');
    }

    if ((int)date('i', $startTimestamp) % 15 !== 0) {
        throw new Exception('กรุณาเลือกเวลาเป็นช่วง 15 นาที (เช่น 09:00, 09:15, 09:30)');
    }

    // Check staff availability
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM booking
        WHERE staff_id = ? AND booking_date = ?
        AND (
            (time_start <= ? AND time_end > ?) OR
            (time_start < ? AND time_end >= ?) OR
            (time_start >= ? AND time_end <= ?)
        )
    ");
    $stmt->execute([$staff_id, $booking_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('ผู้ให้บริการไม่ว่างในช่วงเวลานี้');
    }

    // Insert into booking with evidence
    $stmt = $pdo->prepare("
        INSERT INTO booking (customer_id, staff_id, booking_date, time_start, time_end, total_price, total_discount, final_price, status, discount_detail, evidence)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $customer_id, 
        $staff_id, 
        $booking_date, 
        $start_time, 
        $end_time,
        $total_price,
        $total_discount,
        $final_price,
        BOOKING_STATUS_PENDING,
        $promotionSummary !== '' ? $promotionSummary : null,
        $evidenceFileName
    ]);
    $booking_id = $pdo->lastInsertId();

    // Insert into booking_seviceop
    foreach ($options as $option_id) {
        $optionId = (int)$option_id;
        $pricing = $optionPricing[$optionId] ?? null;

        if ($pricing === null) {
            $stmt = $pdo->prepare("SELECT price FROM service_option WHERE option_id = ?");
            $stmt->execute([$optionId]);
            $optionRow = $stmt->fetch();
            if (!$optionRow) {
                throw new Exception('ไม่พบข้อมูลบริการที่เลือก');
            }
            $price = (float)$optionRow['price'];
            $discount = 0.0;
            $net_price = $price;
        } else {
            $price = (float)$pricing['price'];
            $discount = (float)$pricing['discount'];
            $net_price = (float)$pricing['net'];
        }

        $stmt = $pdo->prepare("INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $optionId, $price, $discount, $net_price]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'จองสำเร็จ รอการตรวจสอบการชำระเงิน']);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Delete uploaded file if there's an error
    if (isset($evidenceFileName) && $evidenceFileName && file_exists('../admin/assets/img/' . $evidenceFileName)) {
        unlink('../admin/assets/img/' . $evidenceFileName);
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>