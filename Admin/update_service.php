<?php
require_once("connect_db.php");

$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$name = mysqli_real_escape_string($conn, $_POST['service_name']);
$detail = mysqli_real_escape_string($conn, $_POST['service_detail']);
$active = isset($_POST['active_status']) ? (int)$_POST['active_status'] : 0;
$service_discount = 0;
$targetDir = "assets/img/";
$updateCoverImg = ""; // สำหรับเตรียม SQL เงื่อนไข coverimg

if (isset($_FILES["imgservice"]) && $_FILES["imgservice"]["error"] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES["imgservice"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["imgservice"]["tmp_name"], $targetFilePath)) {
        $updateCoverImg = ", coverimg = '$fileName'";
    } else {
        die("Error uploading new image.");
    }
}

// 1. อัปเดตข้อมูล service
$sql = "UPDATE service
        SET service_name = '$name',
            description = '$detail',
            is_active = '$active',
            discount_percent = '$service_discount'" .
        $updateCoverImg . "
        WHERE service_id = '$service_id'";

mysqli_query($conn, $sql);

// 2. อัปเดตเวลา-ราคาที่มีอยู่เดิม
if (isset($_POST['existing_times']) && isset($_POST['existing_prices'])) {
  foreach ($_POST['existing_times'] as $time_id => $duration) {
    $price = $_POST['existing_prices'][$time_id];

    $d = intval($duration);
    $p = floatval($price);

    $sql = "UPDATE service_option
            SET duration = '$d', price = '$p', discount_percent = '0'
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
      $sql = "INSERT INTO service_option (service_id, duration, price, discount_percent)
              VALUES ('$service_id', '$t', '$p', '0')";
      mysqli_query($conn, $sql);
    }
  }
}
echo "<script>alert('Update completed'); window.location='table_service.php';</script>";
?>
