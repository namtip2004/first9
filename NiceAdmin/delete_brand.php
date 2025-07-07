<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล


    $brand_id = $_GET['id'];

    $sql="delete from brand where brand_id=$brand_id";

    mysqli_query($conn,$sql);
?>
<meta http-equiv="refresh" content="2; url=tables-brand.php">
