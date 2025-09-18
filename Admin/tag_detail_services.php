<?php
require_once("connect_db.php");

$tag_id = isset($_GET['tag_id']) ? (int)$_GET['tag_id'] : 0;

if ($tag_id > 0) {
    $sql = "SELECT s.service_name FROM service s
            INNER JOIN tag_service ts ON s.service_id = ts.service_id
            WHERE ts.tag_id = $tag_id";

    $result = mysqli_query($conn, $sql);
    $services = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($services);
} else {
    echo json_encode([]);
}
?>
