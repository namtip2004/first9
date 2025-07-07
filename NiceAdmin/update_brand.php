<?php
require_once "connect_db.php"; // เรียกใช้ไฟล์สำหรับเชื่อมต่อฐานข้อมูล
$statusMsg = ''; // ตัวแปรสำหรับแจ้งสถานะการอัปเดต

$brand_id = $_GET['id']; // รับ brand_id จาก URL
$brand_name = $_POST['BrandName']; // รับชื่อแบรนด์จากฟอร์ม

if (!empty($brand_id) && !empty($brand_name)) {
    // สร้างคำสั่ง SQL สำหรับการอัปเดตข้อมูล
    $sql = "UPDATE brand SET brand_name = '$brand_name' WHERE brand_id = '$brand_id'";

    // รันคำสั่ง SQL
    if (mysqli_query($conn, $sql)) {
        $statusMsg = "Brand has been updated successfully."; // อัปเดตสำเร็จ
    } else {
        $statusMsg = "Error updating brand: " . mysqli_error($conn); // เกิดข้อผิดพลาด
    }
} else {
    $statusMsg = "Brand name or ID cannot be empty."; // แจ้งว่าชื่อแบรนด์หรือ ID ว่าง
}

// แสดงข้อความสถานะ
echo $statusMsg;
?>
<meta http-equiv="refresh" content="2; url=tables-brand.php">
