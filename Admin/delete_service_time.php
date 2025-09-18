<?php
require_once("connect_db.php");
$id = $_GET['id'];
$service_id = $_GET['service'];

// ลบเวลา
mysqli_query($conn, "DELETE FROM service_option WHERE option_id = '$id'");
echo "<script>window.location='service_update_from.php?id=$service_id';</script>";
?>
