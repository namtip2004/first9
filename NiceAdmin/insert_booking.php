<?php
include("connect_db.php"); // เปลี่ยนตามชื่อจริงของคุณ

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['member_ID']) &&
        isset($_POST['booking_date']) &&
        isset($_POST['staff_ID']) &&
        isset($_POST['time_start']) &&
        isset($_POST['courses']) &&
        is_array($_POST['courses'])
    ) {
        $member_ID = $_POST['member_ID'];
        $booking_date = $_POST['booking_date'];
        $staff_ID = $_POST['staff_ID'];
        $time_start = $_POST['time_start'];
        $course_IDs = $_POST['courses'];

        $total_time = "00:00:00";
        $total_price = 0.0;
        $course_times = [];

        foreach ($course_IDs as $course_ID) {
            $cid = intval($course_ID);

            // ดึงเวลาและราคา
            $sql = "SELECT c.course_price, t.Time FROM course c JOIN time t ON c.course_ID = t.course_ID WHERE c.course_ID = $cid";
            $result = mysqli_query($conn, $sql);

            if ($row = mysqli_fetch_assoc($result)) {
                $total_price += floatval($row['course_price']);
                $total_time = sumTimes($total_time, $row['Time']);
                $course_times[] = ['id' => $cid, 'time' => $row['Time']];
            } else {
                echo "ไม่พบข้อมูลคอร์ส ID: $cid";
                exit();
            }
        }

        // บันทึกลง booking
        $sql_booking = "INSERT INTO booking (member_ID, booking_date, total, booking_status, staff_ID, time_total, time_start)
                        VALUES (?, ?, ?, 'pending', ?, ?, ?)";
        $stmt = $conn->prepare($sql_booking);
        $stmt->bind_param("isdiss", $member_ID, $booking_date, $total_price, $staff_ID, $total_time, $time_start);
        $stmt->execute();

        $booking_ID = $stmt->insert_id;

        // บันทึก booking_detail
        foreach ($course_times as $ct) {
            $stmt_d = $conn->prepare("INSERT INTO booking_detail (course_ID, booking_ID, Book_D_time) VALUES (?, ?, ?)");
            $stmt_d->bind_param("iis", $ct['id'], $booking_ID, $ct['time']);
            $stmt_d->execute();
        }

        echo "✅ จองสำเร็จ!";
        header("Location: booking_success.php");
        exit();

    } else {
        echo "❌ ข้อมูลไม่ครบ!";
        print_r($_POST); // DEBUG
        exit();
    }
}

// ฟังก์ชันรวมเวลา
function sumTimes($time1, $time2) {
    [$h1, $m1, $s1] = explode(':', $time1);
    [$h2, $m2, $s2] = explode(':', $time2);

    $total = ($h1*3600 + $m1*60 + $s1) + ($h2*3600 + $m2*60 + $s2);

    $hours = floor($total / 3600);
    $minutes = floor(($total % 3600) / 60);
    $seconds = $total % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}
?>
