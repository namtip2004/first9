<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$promotion_id = $_GET['promotion_id'];
$service_id = $_GET['service_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM promotion_service WHERE promotion_id = ? AND service_id = ?");
$stmt->execute([$promotion_id, $service_id]);
echo json_encode($stmt->fetchColumn() > 0);
?>