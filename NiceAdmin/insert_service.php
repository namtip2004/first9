<?php
require_once("connect_db.php");

$name = $_POST['course_name'];
$detail = $_POST['course_detail'];
$active = $_POST['active_status'];

// File upload path
$targetDir = "assets/img/";
$fileName = basename($_FILES["imgservice"]["name"]); //มีเพิ่มในฟอร์มนิดหน่อย
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION); 
if (isset($_FILES["imgservice"]) && $_FILES["imgservice"]["error"] === UPLOAD_ERR_OK) {
    // อัปโหลดไฟล์
    if (move_uploaded_file($_FILES["imgservice"]["tmp_name"], $targetFilePath)) {

        $stmt = $conn->prepare("INSERT INTO service (service_name, description, is_active, coverimg) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $detail, $active, $fileName);
        $stmt->execute();

        $service_id = $stmt->insert_id; // ต้องได้ค่าที่แน่ชัด
        $stmt->close();

        $statusMsg = "Upload success and service inserted.";
    } else {
        die("Error uploading file.");
    }
} else {
    die("Please select a file to upload.");
}



// 2. Get inserted course_ID
$service_id = mysqli_insert_id($conn);

// 3. Insert times
if (!empty($service_id) && !empty($_POST['new_times']) && !empty($_POST['new_prices'])) {
    $times = $_POST['new_times'];
    $prices = $_POST['new_prices'];

    for ($i = 0; $i < count($times); $i++) {
        $t = $times[$i];
        $p = $prices[$i];

        if ($t !== '' && $p !== '') {
            $sql = "INSERT INTO service_option (service_id, duration, price) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iid", $service_id, $t, $p);
            $stmt->execute();
            $stmt->close();
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
