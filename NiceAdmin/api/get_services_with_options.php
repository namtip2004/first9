<?php
require_once("connect_db.php");
header("Content-Type: application/json");

$stmt = $conn->prepare("SELECT s.service_id, s.service_name FROM service s WHERE is_active = 1");
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($services as &$service) {
  $stmt = $conn->prepare("SELECT option_id, duration, price FROM service_option WHERE service_id = ?");
  $stmt->bind_param("i", $service['service_id']);
  $stmt->execute();
  $service['options'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

echo json_encode($services); // คืน array เสมอ
$conn->close();
?>