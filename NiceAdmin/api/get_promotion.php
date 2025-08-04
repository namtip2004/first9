<?php
ob_start();
require_once("connect_db.php");
header("Content-Type: application/json; charset=utf-8");

// Set timezone to ensure consistent date handling
date_default_timezone_set('Asia/Bangkok');

$promotions = [];
try {
    $current_date = date("Y-m-d");
    error_log("Current date in get_promotion.php: " . $current_date);
    $stmt = $conn->prepare("
        SELECT p.promotion_id, p.discount, p.apply_to_all, p.pm_start_date, p.pm_end_date, GROUP_CONCAT(ps.service_id) as service_ids
        FROM promotion p
        LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
        WHERE p.active = '1' AND p.pm_start_date <= ? AND p.pm_end_date >= ?
        GROUP BY p.promotion_id
    ");
    $stmt->bind_param("ss", $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    error_log("Query executed, rows returned: " . $result->num_rows);
    while ($row = $result->fetch_assoc()) {
        $row['service_ids'] = $row['service_ids'] ? array_map('intval', explode(',', $row['service_ids'])) : [];
        $row['discount'] = floatval($row['discount']);
        $row['apply_to_all'] = intval($row['apply_to_all']);
        $promotions[] = $row;
        error_log("Promotion found: " . json_encode($row));
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error in get_promotion.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    ob_end_clean();
    exit;
}

$conn->close();
ob_end_clean();
echo json_encode($promotions);
?>