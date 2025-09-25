<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $date = $_GET['date'] ?? '';
    $duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 0;

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $duration <= 0) {
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

    $openTimestamp = strtotime($date . ' ' . $businessHours['open_time']);
    $closeTimestamp = strtotime($date . ' ' . $businessHours['close_time']);

    if ($openTimestamp === false || $closeTimestamp === false || $closeTimestamp <= $openTimestamp) {
        echo json_encode([]);
        exit;
    }

    $interval = 15 * 60;
    $durationSeconds = $duration * 60;

    // Align the start time to the next 15-minute slot if needed
    $startTimestamp = $openTimestamp;
    $offset = $startTimestamp % $interval;
    if ($offset !== 0) {
        $startTimestamp += ($interval - $offset);
    }

    $times = [];
    $availabilityStmt = $pdo->prepare("
        SELECT COUNT(*) FROM staff s
        WHERE s.st_status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM booking b
            WHERE b.staff_id = s.staff_id
            AND b.booking_date = ?
            AND (
                (b.time_start <= ? AND b.time_end > ?)
                OR (b.time_start < ? AND b.time_end >= ?)
            )
        )
    ");

    for ($time = $startTimestamp; $time + $durationSeconds <= $closeTimestamp; $time += $interval) {
        $startTime = date('H:i', $time);
        $endTime = date('H:i', $time + $durationSeconds);

        $availabilityStmt->execute([$date, $startTime, $startTime, $endTime, $endTime]);
        if ((int)$availabilityStmt->fetchColumn() > 0) {
            $times[] = $startTime;
        }
    }

    echo json_encode($times);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([]);
}
