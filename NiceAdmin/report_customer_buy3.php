<?php

require_once("connect_db.php");

$sqlQuery = "SELECT SUM(grand_total) as cnt
            FROM orders WHERE customer_id = '". $_SESSION['customer_id']."' AND STATUS=3" ;

$result = mysqli_query($conn,$sqlQuery);
$row = mysqli_fetch_assoc($result);
echo $row['cnt']

?>
