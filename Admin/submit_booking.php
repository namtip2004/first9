<?php
require_once 'promotion_utils.php';
require_once 'connect_db.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=first9', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->beginTransaction();

try {
    $customer_id = (int) ($_POST['customer_id'] ?? 0);
    $staff_id = (int) ($_POST['staff_id'] ?? 0);
    $booking_date = $_POST['booking_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $services = $_POST['services'] ?? [];
    $options = $_POST['options'] ?? [];
    $targetDir = 'assets/img/';

    if ($customer_id <= 0 || $staff_id <= 0 || empty($booking_date) || empty($start_time) || empty($options)) {
        throw new Exception('กรุณากรอกข้อมูลการจองให้ครบถ้วน');
    }

    $evidence_image = 'no_image.jpg';
    if (isset($_FILES['imgprofile']) && $_FILES['imgprofile']['error'] == 0) {
        $fileName = basename($_FILES['imgprofile']['name']);
        $targetFilePath = $targetDir . uniqid() . '_' . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024;

        if (in_array($fileType, $allowedTypes) && $_FILES['imgprofile']['size'] <= $maxFileSize) {
            if (move_uploaded_file($_FILES['imgprofile']['tmp_name'], $targetFilePath)) {
                $evidence_image = basename($targetFilePath);
            } else {
                throw new Exception('Failed to upload image.');
            }
        } else {
            throw new Exception('Invalid file type or size.');
        }
    }

    $total_duration = 0;
    $total_price = 0.0;
    $optionData = [];
    $optionIds = [];

    foreach ($options as $index => $option_id) {
        $option_id = (int) $option_id;
        $stmt = $pdo->prepare('SELECT duration, price FROM service_option WHERE option_id = ?');
        $stmt->execute([$option_id]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$option) {
            throw new Exception('Invalid option_id: ' . $option_id);
        }
        $duration = (int) $option['duration'];
        $price = (float) $option['price'];
        $total_duration += $duration;
        $total_price += $price;
        $optionData[$option_id] = [
            'duration' => $duration,
            'price' => $price
        ];
        $optionIds[] = $option_id;
    }

    $end_time = date('H:i', strtotime('+' . $total_duration . ' minutes', strtotime($start_time)));

    $bookingMoment = date('Y-m-d H:i:s');
    $discountMap = [];
    $total_discount = 0.0;
    $discount_detail = '';

    if (!empty($optionIds)) {
        ensurePromotionSupport($conn);
        $result = getApplicableOptionDiscounts($conn, $optionIds, $bookingMoment);
        $discountMap = $result['by_option'];
        $total_discount = (float) $result['total_discount'];
        $discount_detail = summarizePromotionDiscountDetail($result['by_promotion']);
    }

    $final_price = $total_price - $total_discount;

    $stmt = $pdo->prepare(
        'INSERT INTO booking (customer_id, staff_id, booking_date, time_start, time_end, total_price, total_discount, final_price, status, discount_detail, evidence)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $customer_id,
        $staff_id,
        $booking_date,
        $start_time,
        $end_time,
        $total_price,
        $total_discount,
        $final_price,
        BOOKING_STATUS_CONFIRMED,
        $discount_detail,
        $evidence_image
    ]);
    $booking_id = $pdo->lastInsertId();

    $insertOptionStmt = $pdo->prepare('INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price) VALUES (?, ?, ?, ?, ?)');

    foreach ($optionIds as $option_id) {
        $price = $optionData[$option_id]['price'];
        $discountInfo = $discountMap[$option_id] ?? null;
        $discountAmount = $discountInfo ? (float) $discountInfo['discount_amount'] : 0.0;
        $netPrice = $price - $discountAmount;

        $insertOptionStmt->execute([$booking_id, $option_id, $price, $discountAmount, $netPrice]);
    }

    $pdo->commit();
    header('Location: table_booking.php');
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo 'Error: ' . $e->getMessage();
}
