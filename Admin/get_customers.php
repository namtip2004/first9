<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$search = $_GET['search'] ?? '';

$stmt = $pdo->prepare("
    SELECT customer_id, CONCAT( customer_name AS customer_name
    FROM customer
    WHERE is_active = 1 AND (customer_name  ?)
");
$stmt->execute(["%$search%", "%$search%", "%$search%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
