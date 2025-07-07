<?php
// Include the database configuration file
require_once "connect_db.php"; //อย่าลืมแก้ถ้าตั้งไว้ไม่เหมือน
$statusMsg = '';
echo "<script>alert('update delivery status')</script>";

$order_id = $_POST['order_id'];
$delivery_date = $_POST['delivery_date'];
$delivery_code = $_POST['delivery_code'];
//$bank = $_POST['bank']; แจ้งว่าโอนมาขากธนาคารไหน


            // update data in order table
            $query = "update orders set status='3',delivery_date='".$delivery_date."',
                        delivery_code='".$delivery_code."'";
            $query .=" where order_id = '".$order_id."'";

            mysqli_query($conn,$query);

            echo $query;
            


?>
<meta http-equiv="refresh" content = "0; url = tables-data-orders-admin.php">