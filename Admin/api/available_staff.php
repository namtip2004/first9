<?php
header('Content-Type: application/json');
include("../connect_db.php");
$cancelledStatus = BOOKING_STATUS_CANCELLED;

$date = $_GET['date'] ?? '';
$start_time = $_GET['time'] ?? '';
$duration = $_GET['duration'] ?? '';

if (!$date || !$start_time || !$duration) {
    echo json_encode([]);
    exit;
}

try {
    $start_datetime = new DateTime("$date $start_time");
    $duration_parts = explode(":", $duration);
    $interval_spec = "PT{$duration_parts[0]}H{$duration_parts[1]}M";
    $interval = new DateInterval($interval_spec);
    $end_datetime = clone $start_datetime;
    $end_datetime->add($interval);

    $start_str = $start_datetime->format('Y-m-d H:i:s');
    $end_str = $end_datetime->format('Y-m-d H:i:s');

    // ดึง staff active ทั้งหมด
    $staff_query = "SELECT * FROM staff WHERE staff_status = 'active'";
    $staff_result = mysqli_query($conn, $staff_query);
    $available_staff = [];

    while ($staff = mysqli_fetch_assoc($staff_result)) {
        $staff_ID = (int)$staff['staff_ID'];

        // เช็ค booking ทับซ้อน
        $conflict_query = "
            SELECT 1 FROM booking
            WHERE staff_ID = $staff_ID
              AND booking_status != {$cancelledStatus}
              AND time_start < '$end_str'
              AND ADDTIME(time_start, time_total) > '$start_str'
            LIMIT 1
        ";

        $conflict_result = mysqli_query($conn, $conflict_query);

        if (mysqli_num_rows($conflict_result) === 0) {
            // ไม่มี booking ทับซ้อน แปลว่าว่าง
            $available_staff[] = $staff;
        }
    }

    echo json_encode($available_staff);
} catch (Exception $e) {
    echo json_encode([]);
}
exit;
