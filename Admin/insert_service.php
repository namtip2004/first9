<?php
require_once("connect_db.php");

$name = $_POST['course_name'];
$detail = $_POST['course_detail'];
$active = isset($_POST['active_status']) ? (int)$_POST['active_status'] : 0;
$service_discount = 0;

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
$service_id = $service_id ?: mysqli_insert_id($conn);

// 3. Insert times
if (!empty($service_id) && !empty($_POST['new_times']) && !empty($_POST['new_prices'])) {
    $times = $_POST['new_times'];
    $prices = $_POST['new_prices'];

    for ($i = 0; $i < count($times); $i++) {
        $t = isset($times[$i]) ? intval($times[$i]) : 0;
        $p = isset($prices[$i]) ? floatval($prices[$i]) : 0;
        $d = 0;

        if ($t > 0 && $p > 0) {
            $sql = "INSERT INTO service_option (service_id, duration, price) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iid", $service_id, $t, $p);
            $stmt->execute();
            $stmt->close();
        }
    }
}

echo "<script>alert('add completed'); window.location='table_service.php';</script>";
?>
