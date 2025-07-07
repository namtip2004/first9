<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $product_id = $_GET['id'];

    $sql="delete from products where id=$product_id";

    mysqli_query($conn,$sql);
?>
<meta http-equiv="refresh" content="2; url=tables-data-product-admin.php">
