<?php
require_once("../connect_db.php");
header("Content-Type: application/json");

if (!isset($_POST['date'], $_POST['time'], $_POST['duration'])) {
  echo json_encode([]);
  exit;
}

$date = $_POST['date'];
$start = $_POST['time'];
$duration = intval($_POST['duration']);
$end = date("H:i", strtotime($start) + ($duration * 60));

$availableStaff = [];
$q = "SELECT * FROM staff WHERE st_status = 'active'";
$res = $conn->query($q);

while ($staff = $res->fetch_assoc()) {
  $sid = $staff['staff_id'];
  $conflict = $conn->query("SELECT * FROM booking 
    WHERE booking_date = '$date' AND staff_id = $sid
    AND (time_start < '$end' AND time_end > '$start')");
  if ($conflict->num_rows === 0) {
    $availableStaff[] = $staff;
  }
}

echo json_encode($availableStaff);
