<?php
require_once("../connect_db.php");
header("Content-Type: application/json");

if (!isset($_POST['date'], $_POST['duration'])) {
  echo json_encode([]);
  exit;
}

$date = $_POST['date'];
$duration = intval($_POST['duration']);
$open = "08:00:00";
$close = "18:00:00";

$times = [];
$start = strtotime($open);
$end = strtotime($close) - ($duration * 60);

while ($start <= $end) {
  $slot_start = date("H:i", $start);
  $slot_end = date("H:i", $start + ($duration * 60));

  $sql = "SELECT * FROM booking 
          WHERE booking_date = '$date'
          AND (time_start < '$slot_end' AND time_end > '$slot_start')";
  $res = $conn->query($sql);
  $maxStaff = $conn->query("SELECT COUNT(*) as c FROM staff WHERE st_status = 'active'")->fetch_assoc()['c'];

  if ($res->num_rows < $maxStaff) {
    $times[] = $slot_start;
  }

  $start += 15 * 60;
}

echo json_encode($times);
