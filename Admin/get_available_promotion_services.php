<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

header('Content-Type: application/json; charset=utf-8');

$startInput = $_GET['start'] ?? '';
$endInput = $_GET['end'] ?? '';
$promotionId = isset($_GET['promotion_id']) ? (int) $_GET['promotion_id'] : null;

$start = parseDateTimeValue($startInput);
$end = parseDateTimeValue($endInput);

if (!$start || !$end || $end <= $start) {
    echo json_encode([]);
    exit;
}

$startString = $start->format('Y-m-d H:i:s');
$endString = $end->format('Y-m-d H:i:s');

ensurePromotionSupport($conn);
$services = getAvailablePromotionServices($conn, $startString, $endString, $promotionId);

echo json_encode($services, JSON_UNESCAPED_UNICODE);
