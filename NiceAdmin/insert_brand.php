<?php
require_once "connect_db.php"; // เชื่อมต่อฐานข้อมูล
$statusMsg = ''; // ประกาศตัวแปรสถานะ

// รับข้อมูลจากแบบฟอร์ม
$brand_name = $_POST['BrandName'];

// ตรวจสอบว่า brand_name ไม่ว่างเปล่า
if (!empty($brand_name)) {
    // สร้างคำสั่ง SQL สำหรับการเพิ่มข้อมูล
    $sql = "INSERT INTO brand (brand_name) VALUES ('$brand_name')";

    // รันคำสั่ง SQL
    if (mysqli_query($conn, $sql)) {
        $statusMsg = "Brand has been added successfully.";
    } else {
        $statusMsg = "Error adding brand: " . mysqli_error($conn);
    }
} else {
    $statusMsg = "Brand name cannot be empty.";
}

// แสดงผลสถานะ
echo $statusMsg;
?>
<meta http-equiv="refresh" content="2; url=tables-brand.php">
