<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: table_promotion.php?error=' . urlencode('ไม่สามารถเพิ่มโปรโมชั่นได้')); 
    exit;
}

ensurePromotionSupport($conn);

function redirectWithMessage(string $type, string $message): void
{
    header('Location: table_promotion.php?' . $type . '=' . urlencode($message));
    exit;
}

$pmName = isset($_POST['pm_name']) ? trim($_POST['pm_name']) : '';
$startInput = $_POST['pm_start_date'] ?? '';
$endInput = $_POST['pm_end_date'] ?? '';
$payloadJson = $_POST['promotion_payload'] ?? '';

if ($pmName === '' || $startInput === '' || $endInput === '' || $payloadJson === '') {
    redirectWithMessage('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
}

$start = parseDateTimeValue($startInput);
$end = parseDateTimeValue($endInput);
if (!$start || !$end || $end <= $start) {
    redirectWithMessage('error', 'กรุณาระบุวันและเวลาให้ถูกต้อง');
}

$payload = json_decode($payloadJson, true);
if (!is_array($payload)) {
    redirectWithMessage('error', 'ไม่สามารถอ่านข้อมูลบริการได้');
}

try {
    $normalized = normalizePromotionPayload($conn, $payload);
} catch (InvalidArgumentException $e) {
    redirectWithMessage('error', $e->getMessage());
}

$services = $normalized['services'];
$maxPercent = (float) $normalized['max_percent'];

$serviceIds = array_map(static fn($service) => (int) $service['service_id'], $services);
$startString = $start->format('Y-m-d H:i:s');
$endString = $end->format('Y-m-d H:i:s');

$conflicts = findConflictingServiceIds($conn, $serviceIds, $startString, $endString, null);
if (!empty($conflicts)) {
    redirectWithMessage('error', 'ไม่สามารถจัดโปรโมชั่นซ้ำกับบริการที่มีโปรโมชั่นในช่วงเวลาดังกล่าวได้');
}

$columns = getPromotionColumns($conn);
if (!in_array('pm_name', $columns, true) || !in_array('pm_start_date', $columns, true) || !in_array('pm_end_date', $columns, true)) {
    redirectWithMessage('error', 'โครงสร้างตารางโปรโมชั่นไม่รองรับการบันทึกข้อมูล');
}

$insertColumns = [];
$types = '';
$values = [];

$insertColumns[] = 'pm_name';
$types .= 's';
$values[] = $pmName;

if (in_array('description', $columns, true)) {
    $insertColumns[] = 'description';
    $types .= 's';
    $values[] = '';
}

if (in_array('percent', $columns, true)) {
    $insertColumns[] = 'percent';
    $types .= 'd';
    $values[] = $maxPercent;
} elseif (in_array('discount', $columns, true)) {
    $insertColumns[] = 'discount';
    $types .= 'd';
    $values[] = $maxPercent;
}

if (in_array('apply_to_all', $columns, true)) {
    $insertColumns[] = 'apply_to_all';
    $types .= 'i';
    $values[] = 0;
}

$insertColumns[] = 'pm_start_date';
$types .= 's';
$values[] = $startString;

$insertColumns[] = 'pm_end_date';
$types .= 's';
$values[] = $endString;

if (in_array('active', $columns, true)) {
    $insertColumns[] = 'active';
    $types .= 'i';
    $values[] = 1;
}

if (in_array('pm_created_at', $columns, true)) {
    $insertColumns[] = 'pm_created_at';
    $types .= 's';
    $values[] = date('Y-m-d H:i:s');
}

$placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
$sql = 'INSERT INTO promotion (' . implode(', ', $insertColumns) . ') VALUES (' . $placeholders . ')';

$conn->begin_transaction();
try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('ไม่สามารถบันทึกโปรโมชั่นได้');
    }
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $promotionId = $stmt->insert_id;
    $stmt->close();

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
    redirectWithMessage('message', 'เพิ่มโปรโมชั่นเรียบร้อยแล้ว');
} catch (Throwable $e) {
    $conn->rollback();
    redirectWithMessage('error', 'เกิดข้อผิดพลาดระหว่างบันทึกโปรโมชั่น');
}
