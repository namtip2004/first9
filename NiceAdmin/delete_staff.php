<?php
require_once("connect_db.php");

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $staff_id = (int)$_GET['id'];

    // คำสั่ง SQL สำหรับลบข้อมูล
    $sql = "DELETE FROM staff WHERE staff_ID = $staff_id";

    if (mysqli_query($conn, $sql)) {
        // ลบสำเร็จ กลับไปหน้าตาราง
        header("Location: tables_staff.php");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล: " . mysqli_error($conn);
    }
} else {
    echo "ไม่พบข้อมูลพนักงานที่ต้องการลบ";
}
?>
