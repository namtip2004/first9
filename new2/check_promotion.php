<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $promotionId = $_GET['promotion_id'] ?? '';
    $serviceId = $_GET['service_id'] ?? '';
    
    if (!$promotionId || !$serviceId) {
        echo json_encode(false);
        exit;
    }
    
    // ตรวจสอบว่าโปรโมชั่นใช้ได้กับบริการนี้หรือไม่
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as can_apply
        FROM promotion p
        LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
        WHERE p.promotion_id = :promotion_id
        AND p.active = 1
        AND p.pm_start_date <= NOW()
        AND p.pm_end_date >= NOW()
        AND (p.apply_to_all = 1 OR ps.service_id = :service_id)
    ");
    
    $stmt->execute([
        ':promotion_id' => $promotionId,
        ':service_id' => $serviceId
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result['can_apply'] > 0);
    
} catch (Exception $e) {
    error_log("Error in check_promotion.php: " . $e->getMessage());
    echo json_encode(false);
}
?>
