<?php
header('Content-Type: application/json');


try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
    
    $services = $pdo->query("
        SELECT 
            service_id, 
            service_name, 
            COALESCE(description, 'บริการสปาคุณภาพสูง') as description,
            COALESCE(coverimg, '') as coverimg
        FROM service 
        WHERE is_active = 1 
        ORDER BY service_name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($services);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}
?>