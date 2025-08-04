<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$service_id = $_GET['service_id'];
$stmt = $pdo->prepare("SELECT option_id, duration, price FROM service_option WHERE service_id = ?");
$stmt->execute([$service_id]);
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($options);
?>