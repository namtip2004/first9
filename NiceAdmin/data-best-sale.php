<?php
header('Content-Type: application/json');
include "connect_db.php";
$sqlQuery = "SELECT code,name,SUM(quantity) as sumQty FROM `order_items` 
INNER JOIN products ON products.code = order_items.product_id 
INNER JOIN orders on order_items.order_id = orders.order_id 
WHERE substr(pay_date,1,4) = '2024'
                and status=3 GROUP BY code";

$result = mysqli_query($conn,$sqlQuery);

$data = array();
foreach ($result as $row) {

    $point = array("label" => $row['name'], "y" => $row['sumQty']);
    array_push($data, $point);
}

mysqli_close($conn);
echo json_encode($data, JSON_NUMERIC_CHECK);

?>
