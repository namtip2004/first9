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
      $sql = "INSERT INTO service_option (service_id, duration, price) VALUES ('$service_id', '$time', '$price')";
      mysqli_query($conn, $sql);
    }
  }
}

$tags_string = $_POST['tags'] ?? '';
$tag_array = array_filter(array_map('trim', explode(',', $tags_string)));

foreach ($tag_array as $tag_name) {
  // ตรวจสอบว่าแท็กมีอยู่หรือยัง
  $stmt = $conn->prepare("SELECT tag_id FROM tag WHERE tag_name = ?");
  $stmt->bind_param("s", $tag_name);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tag_id = $row['tag_id'];
  } else {
    // ถ้ายังไม่มี ให้เพิ่มแท็กใหม่
    $stmt_insert = $conn->prepare("INSERT INTO tag (tag_name) VALUES (?)");
    $stmt_insert->bind_param("s", $tag_name);
    $stmt_insert->execute();
    $tag_id = $stmt_insert->insert_id;
    $stmt_insert->close();
  }

  $stmt->close();

  // บันทึกลง tag_service
  $stmt_link = $conn->prepare("INSERT INTO tag_service (service_id, tag_id) VALUES (?, ?)");
  $stmt_link->bind_param("ii", $service_id, $tag_id);
  $stmt_link->execute();
  $stmt_link->close();
}


echo "<script>alert('add completed'); window.location='table_service.php';</script>";
?>
