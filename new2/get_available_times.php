<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

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

    $times = [];
    $start = strtotime("08:00");
    $end = strtotime("18:00");
    $interval = 15 * 60;

    $stmt = $pdo->prepare("
        SELECT staff_id, time_start, time_end 
        FROM booking 
        WHERE booking_date = ?
    ");
    $stmt->execute([$date]);
    $bookings = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT staff_id FROM staff WHERE st_status = 'active'");
    $activeStaff = $stmt->fetchAll(PDO::FETCH_COLUMN);

    for ($time = $start; $time <= $end - $duration * 60; $time += $interval) {
        $start_time = date("H:i", $time);
        $end_time = date("H:i", strtotime("+$duration minutes", $time));
        
        $isAvailable = false;
        foreach ($activeStaff as $staff_id) {
            $hasConflict = false;
            foreach ($bookings as $booking) {
                if ($booking['staff_id'] == $staff_id) {
                    $bookingStart = strtotime($booking['time_start']);
                    $bookingEnd = strtotime($booking['time_end']);
                    $slotStart = strtotime($start_time);
                    $slotEnd = strtotime($end_time);
                    
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