<?php
require_once("connect_db.php");

// Get today's date
$today = date('Y-m-d');

$sqlQuery = "SELECT COUNT(*) as cnt
            FROM orders 
            WHERE STATUS = 3 AND DATE(pay_date) = '$today';";

$result = mysqli_query($conn, $sqlQuery);
$row = mysqli_fetch_assoc($result);
echo $row['cnt'];
?>
