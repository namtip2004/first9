<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'option_discounts' => [], 'promotions' => [], 'total_discount' => 0.0]);
    exit;
}

$optionIds = isset($data['option_ids']) && is_array($data['option_ids']) ? array_map('intval', $data['option_ids']) : [];
$dateValue = $data['date'] ?? '';
$timeValue = $data['time'] ?? '';

if (empty($optionIds) || empty($dateValue)) {
    echo json_encode(['success' => true, 'option_discounts' => [], 'promotions' => [], 'total_discount' => 0.0]);
    exit;
}

$target = combineDateAndTime($dateValue, $timeValue);
if (!$target) {
    echo json_encode(['success' => true, 'option_discounts' => [], 'promotions' => [], 'total_discount' => 0.0]);
    exit;
}

ensurePromotionSupport($conn);
$result = getApplicableOptionDiscounts($conn, $optionIds, $target);

$response = [
    'success' => true,
    'option_discounts' => $result['by_option'],
    'promotions' => $result['by_promotion'],
    'total_discount' => $result['total_discount'],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
