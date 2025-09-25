<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    if (!isset($_GET['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing date']);
        exit;
    }
    $date = $_GET['date'];

    if (!isset($_GET['duration']) || !is_numeric($_GET['duration']) || (int)$_GET['duration'] <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing duration']);
        exit;
    }
    $duration = (int)$_GET['duration'];

    date_default_timezone_set('Asia/Bangkok');

    $dayOfWeek = date('l', strtotime($date));
    $stmt = $pdo->prepare("SELECT open_time, close_time, is_closed FROM business_hours WHERE day_of_week = ?");
    $stmt->execute([$dayOfWeek]);
    $businessHours = $stmt->fetch();

    if (!$businessHours) {
        echo json_encode([]);
        exit;
    }

    if ((int)$businessHours['is_closed'] === 1) {
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

    $startTimestamp = $openTimestamp;
    $offset = $startTimestamp % $interval;
    if ($offset !== 0) {
        $startTimestamp += ($interval - $offset);
    }

    $times = [];

    $stmt = $pdo->prepare("
        SELECT staff_id, time_start, time_end
        FROM booking
        WHERE booking_date = ?
    ");
    $stmt->execute([$date]);
    $bookings = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT staff_id FROM staff WHERE st_status = 'active'");
    $activeStaff = $stmt->fetchAll(PDO::FETCH_COLUMN);

    for ($time = $startTimestamp; $time + $durationSeconds <= $closeTimestamp; $time += $interval) {
        $start_time = date('H:i', $time);
        $end_time = date('H:i', $time + $durationSeconds);

        $isAvailable = false;
        foreach ($activeStaff as $staff_id) {
            $hasConflict = false;
            foreach ($bookings as $booking) {
                if ($booking['staff_id'] == $staff_id) {
                    $bookingStart = strtotime($date . ' ' . $booking['time_start']);
                    $bookingEnd = strtotime($date . ' ' . $booking['time_end']);
                    $slotStart = strtotime($date . ' ' . $start_time);
                    $slotEnd = strtotime($date . ' ' . $end_time);

                    if (($slotStart >= $bookingStart && $slotStart < $bookingEnd) ||
                        ($slotEnd > $bookingStart && $slotEnd <= $bookingEnd) ||
                        ($slotStart <= $bookingStart && $slotEnd >= $bookingEnd)) {
                        $hasConflict = true;
                        break;
                    }
                }
            }
            if (!$hasConflict) {
                $isAvailable = true;
                break;
            }
        }

        if ($isAvailable) {
            $times[] = $start_time;
        }
    }

    echo json_encode($times);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
