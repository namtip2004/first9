<?php
require_once("connect_db.php");

$name = $_POST['course_name'];
$detail = $_POST['course_detail'];
$active = $_POST['active_status'];

// 1. Insert course
$sql = "INSERT INTO service (service_name, description, is_active) 
        VALUES ('$name', '$detail', '$active')";
mysqli_query($conn, $sql);

// 2. Get inserted course_ID
$service_id = mysqli_insert_id($conn);

// 3. Insert times
if (!empty($_POST['new_times']) && !empty($_POST['new_prices'])) {
  $times = $_POST['new_times'];
  $prices = $_POST['new_prices'];

  for ($i = 0; $i < count($times); $i++) {
    $t = $times[$i];
    $p = $prices[$i];

    if ($t !== '' && $p !== '') {
      $time = mysqli_real_escape_string($conn, $t);
      // แปลงราคาเป็น float เพื่อความปลอดภัย
      $price = floatval($p);

      // ใช้ prepared statement จะปลอดภัยกว่าครับ แต่ถ้าใช้แบบนี้ก็พอได้
      $sql = "INSERT INTO service_option (service_id, duration, price) VALUES ('$service_id', '$time', '$prices')";
      mysqli_query($conn, $sql);
    }
  }
}

echo "<script>alert('เพิ่มข้อมูลคอร์สเรียบร้อย'); window.location='table_service.php';</script>";
?>
