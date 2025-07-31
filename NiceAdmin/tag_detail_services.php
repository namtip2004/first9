<?php
require_once("connect_db.php");

if (!isset($_GET['tag_id'])) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$tag_id = intval($_GET['tag_id']);

// ดึงชื่อบริการที่ใช้ tag นี้ (join service กับ tag_service)
$sql = "
  SELECT s.service_name
  FROM service s
  JOIN tag_service ts ON s.service_id = ts.service_id
  WHERE ts.tag_id = $tag_id
";
$result = $conn->query($sql);

$services = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($services);
