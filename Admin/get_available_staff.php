<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$date = $_GET['date'];
$start_time = $_GET['start_time'];
$duration = (int)$_GET['duration'];
$end_time = date("H:i", strtotime("+$duration minutes", strtotime($start_time)));

$stmt = $pdo->prepare("
    SELECT staff_id, staff_name FROM staff
    WHERE st_status = 'active'
    AND NOT EXISTS (
        SELECT 1 FROM booking b
        WHERE b.staff_id = staff.staff_id
        AND b.booking_date = ?
        AND (
            (b.time_start <= ? AND b.time_end > ?)
            OR (b.time_start < ? AND b.time_end >= ?)
        )
    )
");
$stmt->execute([$date, $start_time, $start_time, $end_time, $end_time]);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($staff);
?>