<?php
require_once("connect_db.php");

// Get the current year
$currentYear = date('Y');

$sqlQuery = "SELECT COUNT(*) as cnt
            FROM orders 
            WHERE STATUS = 3 AND YEAR(pay_date) = $currentYear;";

$result = mysqli_query($conn, $sqlQuery);
$row = mysqli_fetch_assoc($result);
echo $row['cnt'];
?>
