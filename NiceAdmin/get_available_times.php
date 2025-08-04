<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$date = $_GET['date'];
$duration = (int)$_GET['duration'];

$times = [];
$start = strtotime("08:00");
$end = strtotime("18:00");
$interval = 15 * 60; // 15 minutes in seconds

for ($time = $start; $time <= $end - $duration * 60; $time += $interval) {
    $start_time = date("H:i", $time);
    $end_time = date("H:i", strtotime("+$duration minutes", $time));
    
    // Check if any staff is available for this time slot
    $stmt = $pdo->prepare("
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
    $stmt->execute([$date, $start_time, $start_time, $end_time, $end_time]);
    if ($stmt->fetchColumn() > 0) {
        $times[] = $start_time;
    }
}

echo json_encode($times);
?>