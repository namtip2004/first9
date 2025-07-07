<?php
require_once("connect_db.php");

// Get the current year and month
$currentYear = date('Y');
$currentMonth = date('m');

$sqlQuery = "SELECT COUNT(*) as cnt
            FROM orders 
            WHERE STATUS = 3 AND YEAR(pay_date) = $currentYear AND MONTH(pay_date) = $currentMonth;";

$result = mysqli_query($conn, $sqlQuery);
$row = mysqli_fetch_assoc($result);
echo $row['cnt'];
?>
