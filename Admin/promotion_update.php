<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

$promotionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($promotionId <= 0) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบโปรโมชั่นที่ต้องการแก้ไข'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: table_promotion.php?error=' . urlencode('ไม่สามารถแก้ไขโปรโมชั่นได้'));
    exit;
}

ensurePromotionSupport($conn);

function redirectUpdate(string $type, string $message, int $promotionId): void
{
    header('Location: table_promotion.php?' . $type . '=' . urlencode($message));
    exit;
}

$stmt = $conn->prepare('SELECT * FROM promotion WHERE promotion_id = ?');
$stmt->bind_param('i', $promotionId);
$stmt->execute();
$currentPromotion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$currentPromotion) {
    redirectUpdate('error', 'ไม่พบโปรโมชั่นที่ต้องการแก้ไข', $promotionId);
}

$status = promotionStatus($currentPromotion['pm_start_date'], $currentPromotion['pm_end_date']);
if ($status === 'ended') {
    redirectUpdate('error', 'ไม่สามารถแก้ไขโปรโมชั่นที่สิ้นสุดแล้วได้', $promotionId);
}

$pmName = isset($_POST['pm_name']) ? trim($_POST['pm_name']) : '';
$startInput = $_POST['pm_start_date'] ?? '';
$endInput = $_POST['pm_end_date'] ?? '';
$payloadJson = $_POST['promotion_payload'] ?? '';

if ($pmName === '' || $startInput === '' || $endInput === '' || $payloadJson === '') {
    redirectUpdate('error', 'กรุณากรอกข้อมูลให้ครบถ้วน', $promotionId);
}

$start = parseDateTimeValue($startInput);
$end = parseDateTimeValue($endInput);
if (!$start || !$end || $end <= $start) {
    redirectUpdate('error', 'กรุณาระบุวันและเวลาให้ถูกต้อง', $promotionId);
}

$payload = json_decode($payloadJson, true);
if (!is_array($payload)) {
    redirectUpdate('error', 'ไม่สามารถอ่านข้อมูลบริการได้', $promotionId);
}

try {
    $normalized = normalizePromotionPayload($conn, $payload);
} catch (InvalidArgumentException $e) {
    redirectUpdate('error', $e->getMessage(), $promotionId);
}

$services = $normalized['services'];
$maxPercent = (float) $normalized['max_percent'];

$serviceIds = array_map(static fn($service) => (int) $service['service_id'], $services);
$startString = $start->format('Y-m-d H:i:s');
$endString = $end->format('Y-m-d H:i:s');

$conflicts = findConflictingServiceIds($conn, $serviceIds, $startString, $endString, $promotionId);
if (!empty($conflicts)) {
    redirectUpdate('error', 'ไม่สามารถจัดโปรโมชั่นซ้ำกับบริการที่มีโปรโมชั่นในช่วงเวลาดังกล่าวได้', $promotionId);
}

$columns = getPromotionColumns($conn);
if (!in_array('pm_name', $columns, true) || !in_array('pm_start_date', $columns, true) || !in_array('pm_end_date', $columns, true)) {
    redirectUpdate('error', 'โครงสร้างตารางโปรโมชั่นไม่รองรับการบันทึกข้อมูล', $promotionId);
}

$updateParts = [];
$types = '';
$values = [];

$updateParts[] = 'pm_name = ?';
$types .= 's';
$values[] = $pmName;

if (in_array('description', $columns, true)) {
    $updateParts[] = 'description = ?';
    $types .= 's';
    $values[] = $currentPromotion['description'] ?? '';
}

if (in_array('percent', $columns, true)) {
    $updateParts[] = 'percent = ?';
    $types .= 'd';
    $values[] = $maxPercent;
} elseif (in_array('discount', $columns, true)) {
    $updateParts[] = 'discount = ?';
    $types .= 'd';
    $values[] = $maxPercent;
}

if (in_array('apply_to_all', $columns, true)) {
    $updateParts[] = 'apply_to_all = 0';
}

$updateParts[] = 'pm_start_date = ?';
$types .= 's';
$values[] = $startString;

$updateParts[] = 'pm_end_date = ?';
$types .= 's';
$values[] = $endString;

if (in_array('active', $columns, true)) {
    $updateParts[] = 'active = 1';
}

$sql = 'UPDATE promotion SET ' . implode(', ', $updateParts) . ' WHERE promotion_id = ?';
$types .= 'i';
$values[] = $promotionId;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('ไม่สามารถบันทึกโปรโมชั่นได้');
    }
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();

    $deleteOptions = $conn->prepare('DELETE FROM promotion_service_option WHERE promotion_id = ?');
    if ($deleteOptions) {
        $deleteOptions->bind_param('i', $promotionId);
        $deleteOptions->execute();
        $deleteOptions->close();
    }

    $deleteServices = $conn->prepare('DELETE FROM promotion_service WHERE promotion_id = ?');
    if ($deleteServices) {
        $deleteServices->bind_param('i', $promotionId);
        $deleteServices->execute();
        $deleteServices->close();
    }

    $serviceStmt = $conn->prepare('INSERT INTO promotion_service (service_id, promotion_id) VALUES (?, ?)');
    $optionStmt = $conn->prepare('INSERT INTO promotion_service_option (promotion_id, service_id, option_id, discount_percent, discount_amount, final_price) VALUES (?, ?, ?, ?, ?, ?)');

    if (!$serviceStmt || !$optionStmt) {
        throw new RuntimeException('ไม่สามารถบันทึกข้อมูลบริการของโปรโมชั่นได้');
    }

    foreach ($services as $service) {
        $serviceId = (int) $service['service_id'];
        $serviceStmt->bind_param('ii', $serviceId, $promotionId);
        $serviceStmt->execute();

        foreach ($service['options'] as $option) {
            $optionId = (int) $option['option_id'];
            $percent = (float) $option['discount_percent'];
            $discountAmount = (float) $option['discount_amount'];
            $finalPrice = (float) $option['final_price'];

            $optionStmt->bind_param('iiiddd', $promotionId, $serviceId, $optionId, $percent, $discountAmount, $finalPrice);
            $optionStmt->execute();
        }
    }

    $serviceStmt->close();
    $optionStmt->close();

    $conn->commit();
    //redirectUpdate('message', 'บันทึกการแก้ไขโปรโมชั่นเรียบร้อยแล้ว', $promotionId);
} catch (Throwable $e) {
    $conn->rollback();
    redirectUpdate('error', 'เกิดข้อผิดพลาดระหว่างบันทึกโปรโมชั่น', $promotionId);
}
