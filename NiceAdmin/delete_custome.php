<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $customer_id = $_GET['id'];

    $sql="delete from customer where customer_id=$customer_id";

    mysqli_query($conn,$sql);
?>
<meta http-equiv="refresh" content="2; url=tables-data-customer-admin.php">
