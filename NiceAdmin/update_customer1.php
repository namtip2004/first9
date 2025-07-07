<?php
require_once("connect_db.php");

if (isset($_GET['id'])) {
  $customer_id = intval($_GET['id']);
  
  // รับข้อมูลจากฟอร์ม
  $customer_name = $_POST['customer_name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $address = $_POST['address'];
  $province_id = $_POST['province_id'];
  $amphur_id = $_POST['amphur_id'];
  $zip = $_POST['zip'];
  
  // อัปเดตข้อมูลในฐานข้อมูล
  $sql = "UPDATE customer 
          SET customer_name = '$customer_name', email = '$email', password = '$password', address = '$address', province_id = $province_id, amphur_id = $amphur_id, zip = '$zip' 
          WHERE customer_id = $customer_id";
          
  if (mysqli_query($conn, $sql)) {
    echo "Customer updated successfully.";
  } else {
    echo "Error updating customer: " . mysqli_error($conn);
  }
}
?>
<meta http-equiv="refresh" content="2; url=tables-data-customer.php">
