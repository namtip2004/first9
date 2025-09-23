<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

header('Content-Type: application/json; charset=utf-8');

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$optionIds = [];
if (!empty($_GET['option_ids'])) {
    $ids = explode(',', $_GET['option_ids']);
    foreach ($ids as $id) {
        $optionIds[] = (int) $id;
    }
}

if (empty($optionIds) || empty($date)) {
    echo json_encode(['success' => true, 'option_discounts' => [], 'total_discount' => 0.0]);
    exit;
}

$target = combineDateAndTime($date, $time);
if (!$target) {
    echo json_encode(['success' => true, 'option_discounts' => [], 'total_discount' => 0.0]);
    exit;
}

ensurePromotionSupport($conn);
$result = getApplicableOptionDiscounts($conn, $optionIds, $target);

echo json_encode([
    'success' => true,
    'option_discounts' => $result['by_option'],
    'total_discount' => $result['total_discount'],
]);
