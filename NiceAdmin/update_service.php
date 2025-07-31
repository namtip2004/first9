<?php
require_once("connect_db.php");

$service_id = $_POST['service_id'];
$name = $_POST['service_name'];
$detail = $_POST['service_detail'];
$active = $_POST['active_status'];

// 1. อัปเดตข้อมูล service
$sql = "UPDATE service 
        SET service_name = '$name', description = '$detail', is_active = '$active'
        WHERE service_id = '$service_id'";
mysqli_query($conn, $sql);

// 2. อัปเดตเวลา-ราคาที่มีอยู่เดิม
if (isset($_POST['existing_times']) && isset($_POST['existing_prices'])) {
  foreach ($_POST['existing_times'] as $time_id => $duration) {
    $price = $_POST['existing_prices'][$time_id];

    $d = intval($duration);
    $p = floatval($price);

    $sql = "UPDATE service_option 
            SET duration = '$d', price = '$p'
            WHERE option_id = '$time_id' AND service_id = '$service_id'";
    mysqli_query($conn, $sql);
  }
}

// 3. เพิ่มเวลาใหม่
if (!empty($_POST['new_times']) && !empty($_POST['new_prices'])) {
  $new_times = $_POST['new_times'];
  $new_prices = $_POST['new_prices'];

  for ($i = 0; $i < count($new_times); $i++) {
    $t = intval($new_times[$i]);
    $p = floatval($new_prices[$i]);

    if ($t > 0 && $p > 0) {
      $sql = "INSERT INTO service_option (service_id, duration, price) 
              VALUES ('$service_id', '$t', '$p')";
      mysqli_query($conn, $sql);
    }
  }
}

echo "<script>alert('Update completed'); window.location='table_service.php';</script>";
?>
