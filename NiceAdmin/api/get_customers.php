<?php
require_once("connect_db.php");
header("Content-Type: application/json");

$stmt = $conn->prepare("SELECT customer_id, customer_name FROM customer WHERE account_status = 'active'");
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($customers); // คืน array เสมอ
$conn->close();
?>