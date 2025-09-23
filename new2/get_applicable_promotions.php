<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Admin/promotion_utils.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    echo json_encode([
        'success' => false,
        'option_discounts' => [],
        'promotions' => [],
        'total_discount' => 0.0,
        'summary' => ''
    ]);
    exit;
}

$optionIds = array_values(array_filter(array_map('intval', $data['option_ids'] ?? []), function ($id) {
    return $id > 0;
}));
$dateValue = isset($data['date']) ? trim((string) $data['date']) : '';
$timeValue = isset($data['time']) ? trim((string) $data['time']) : '';

if (empty($optionIds) || $dateValue === '') {
    echo json_encode([
        'success' => true,
        'option_discounts' => [],
        'promotions' => [],
        'total_discount' => 0.0,
        'summary' => ''
    ]);
    exit;
}

$targetDateTime = combineDateAndTime($dateValue, $timeValue);
if (!$targetDateTime) {
    echo json_encode([
        'success' => true,
        'option_discounts' => [],
        'promotions' => [],
        'total_discount' => 0.0,
        'summary' => ''
    ]);
    exit;
}

$conn = null;

try {
    $conn = new mysqli('127.0.0.1', 'root', '', 'first9');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    ensurePromotionSupport($conn);
    $result = getApplicableOptionDiscounts($conn, $optionIds, $targetDateTime);
    $summary = summarizePromotionDiscountDetail($result['by_promotion']);

    echo json_encode([
        'success' => true,
        'option_discounts' => $result['by_option'],
        'promotions' => $result['by_promotion'],
        'total_discount' => $result['total_discount'],
        'summary' => $summary,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'option_discounts' => [],
        'promotions' => [],
        'total_discount' => 0.0,
        'summary' => '',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
