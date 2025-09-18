<?php
require_once("connect_db.php");

$service_id = $_POST['service_id'];
$name = $_POST['service_name'];
$detail = $_POST['service_detail'];
$active = $_POST['active_status'];
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
            is_active = '$active' 
            $updateCoverImg
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

$tags_string = $_POST['tags'] ?? '';
$tag_array = array_filter(array_map('trim', explode(',', $tags_string)));

// ลบแท็กเก่าทั้งหมดก่อน
$stmt_del = $conn->prepare("DELETE FROM tag_service WHERE service_id = ?");
$stmt_del->bind_param("i", $service_id);
$stmt_del->execute();
$stmt_del->close();

foreach ($tag_array as $tag_name) {
  $stmt = $conn->prepare("SELECT tag_id FROM tag WHERE tag_name = ?");
  $stmt->bind_param("s", $tag_name);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tag_id = $row['tag_id'];
  } else {
    $stmt_insert = $conn->prepare("INSERT INTO tag (tag_name) VALUES (?)");
    $stmt_insert->bind_param("s", $tag_name);
    $stmt_insert->execute();
    $tag_id = $stmt_insert->insert_id;
    $stmt_insert->close();
  }
  $stmt->close();

  $stmt_link = $conn->prepare("INSERT INTO tag_service (service_id, tag_id) VALUES (?, ?)");
  $stmt_link->bind_param("ii", $service_id, $tag_id);
  $stmt_link->execute();
  $stmt_link->close();
}


echo "<script>alert('Update completed'); window.location='table_service.php';</script>";
?>
