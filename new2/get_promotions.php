<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$service_ids = $_GET['service_ids'] ?? '';

if (!$service_ids) {
    echo json_encode([]);
    exit;
}

$service_ids = explode(',', $service_ids);
$placeholders = implode(',', array_fill(0, count($service_ids), '?'));

$today = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("
    SELECT DISTINCT p.promotion_id, p.pm_name, p.discount, p.apply_to_all
    FROM promotion p
    LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
    WHERE 
        p.active = 1 
        AND p.pm_start_date <= ? 
        AND p.pm_end_date >= ?
        AND (p.apply_to_all = 1 OR ps.service_id IN ($placeholders))
");

$params = array_merge([$today, $today], $service_ids);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
