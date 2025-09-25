<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4","root","", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $date       = $_GET['date'] ?? '';
    $start_time = $_GET['start_time'] ?? '';
    $duration   = isset($_GET['duration']) ? (int)$_GET['duration'] : 0;

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $start_time) || $duration <= 0) {
        http_response_code(400);
        echo json_encode([]);
        exit;
    }

    $dayOfWeek = date('l', strtotime($date));
    $stmt = $pdo->prepare("SELECT open_time, close_time, is_closed FROM business_hours WHERE day_of_week = ?");
    $stmt->execute([$dayOfWeek]);
    $businessHours = $stmt->fetch();

    if (!$businessHours || (int)$businessHours['is_closed'] === 1) {
        echo json_encode([]);
        exit;
    }

    $startTimestamp = strtotime($date . ' ' . $start_time);
    $endTimestamp = strtotime("+$duration minutes", $startTimestamp);
    $openTimestamp = strtotime($date . ' ' . $businessHours['open_time']);
    $closeTimestamp = strtotime($date . ' ' . $businessHours['close_time']);

    if ($startTimestamp === false || $endTimestamp === false || $openTimestamp === false || $closeTimestamp === false) {
        echo json_encode([]);
        exit;
    }

    if ($startTimestamp < $openTimestamp || $endTimestamp > $closeTimestamp || $closeTimestamp <= $openTimestamp) {
        echo json_encode([]);
        exit;
    }

    $end_time = date('H:i', $endTimestamp);

    $stmt = $pdo->prepare("\n        SELECT staff_id, staff_name, st_profile\n        FROM staff\n        WHERE st_status = 'active'\n          AND NOT EXISTS (\n            SELECT 1 FROM booking b\n            WHERE b.staff_id = staff.staff_id\n              AND b.booking_date = ?\n              AND (b.time_start < ? AND b.time_end > ?)\n          )\n        ORDER BY staff_name\n    ");
    $stmt->execute([$date, $end_time, $start_time]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $baseUrl = "../admin/assets/img/";

    $out = array_map(function($r) use ($baseUrl) {
        $path = trim($r['st_profile'] ?? '');
        $image_url = $path;
        if ($path !== '') {
            if (!preg_match('#^https?://#i', $path) && strpos($path, '/') !== 0) {
                $image_url = $baseUrl . $path;
            }
        } else {
            $image_url = $baseUrl . "default.png";
        }
        return [
            'staff_id'   => (int)$r['staff_id'],
            'staff_name' => $r['staff_name'],
            'image_url'  => $image_url,
        ];
    }, $rows);

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}
