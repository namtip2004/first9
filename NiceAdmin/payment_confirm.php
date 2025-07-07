<?php
// Include the database configuration file
require_once "connect_db.php";
$statusMsg = '';
echo "<script>alert('update payment status')</script>";

$order_id = $_POST['order_id'];

            // update data in order table
            $query = "update orders set status='2'";
            $query .=" where order_id = '".$order_id."'";
         
            mysqli_query($conn,$query);

            echo $query;
            
?>
<meta http-equiv="refresh" content = "0; url = tables-data-orders-admin.php">