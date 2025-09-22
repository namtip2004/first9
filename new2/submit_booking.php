<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

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

    // รับ Note จากฟอร์ม (textarea name="special_requests") แล้วทำความสะอาด
    $note = '';
    if (isset($_POST['special_requests'])) {
        $note = trim($_POST['special_requests']);
        $note = strip_tags($note);
        $note = mb_substr($note, 0, 1000, 'UTF-8'); // จำกัดความยาวกันสแปม
    }

    // Handle evidence file upload
    $evidenceFileName = null;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../admin/assets/img/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // ใช้ชื่อไฟล์เดิม แต่กันชนกันชื่อซ้ำ
        $originalFileName = $_FILES['evidence']['name'];
        $fileInfo = pathinfo($originalFileName);
        $extension = strtolower($fileInfo['extension']);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('ประเภทไฟล์ไม่ถูกต้อง กรุณาอัพโหลดไฟล์ภาพ (jpg, png, gif)');
        }

        if ($_FILES['evidence']['size'] > 5 * 1024 * 1024) {
            throw new Exception('ไฟล์ขนาดใหญ่เกินไป (สูงสุด 5MB)');
        }

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
    $options  = array_filter(explode(',', $_POST['options']));

    if (count($services) !== count($options)) {
        throw new Exception('ข้อมูลบริการและตัวเลือกไม่สอดคล้องกัน');
    }

    // Calculate total duration and prices
    $total_duration = 0;
    $total_price = 0;
    $total_discount = 0;

    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT duration, price, service_id FROM service_option WHERE option_id = ?");
        $stmt->execute([(int)$option_id]);
        $option = $stmt->fetch();
        if (!$option || $option['service_id'] != $services[$index]) {
            throw new Exception('ตัวเลือกหรือบริการไม่ถูกต้อง');
        }
        $total_duration += (int)$option['duration'];
        $total_price    += (float)$option['price'];
    }

    $end_time = date("H:i", strtotime("+$total_duration minutes", strtotime($start_time)));
    $final_price = $total_price - $total_discount;

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

    // Insert into booking (เพิ่ม note ลงไปด้วย)
    $stmt = $pdo->prepare("
        INSERT INTO booking (
            customer_id, staff_id, booking_date, time_start, time_end,
            total_price, total_discount, final_price, note, status, discount_detail, evidence
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
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
        $note,              // ✅ บันทึกลงคอลัมน์ note
        null,
        $evidenceFileName
    ]);
    $booking_id = $pdo->lastInsertId();

    // Insert into booking_seviceop (สะกดตามชื่อตารางที่คุณใช้อยู่)
    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT price, service_id FROM service_option WHERE option_id = ?");
        $stmt->execute([(int)$option_id]);
        $option = $stmt->fetch();
        $price = (float)$option['price'];
        $discount = 0;

        $net_price = $price - $discount;
        $stmt = $pdo->prepare("
            INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$booking_id, (int)$option_id, $price, $discount, $net_price]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'จองสำเร็จ รอการตรวจสอบการชำระเงิน']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // ลบไฟล์ที่อัปโหลดแล้วหากเกิดข้อผิดพลาด
    if (isset($evidenceFileName) && $evidenceFileName && file_exists('../admin/assets/img/' . $evidenceFileName)) {
        @unlink('../admin/assets/img/' . $evidenceFileName);
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
