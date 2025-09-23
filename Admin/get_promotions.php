<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

header('Content-Type: application/json; charset=utf-8');

$now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

$sql = "SELECT p.promotion_id, p.pm_name, p.pm_start_date, p.pm_end_date
        FROM promotion p
        WHERE p.pm_start_date <= ? AND p.pm_end_date >= ?
        ORDER BY p.pm_start_date";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$result = $stmt->get_result();

$promotions = [];
while ($row = $result->fetch_assoc()) {
    $promotions[] = $row;
}
$stmt->close();

echo json_encode($promotions, JSON_UNESCAPED_UNICODE);
