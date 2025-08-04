<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
    echo "No promotion ID provided.";
    exit;
}

$promotion_id = intval($_GET['id']);

// รับค่าจากฟอร์ม POST และป้องกัน SQL Injection
$pm_name = mysqli_real_escape_string($conn, $_POST['pm_name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$discount = floatval($_POST['discount']);
$apply_to_all = isset($_POST['apply_to_all']) ? 1 : 0;
$pm_start_date = $_POST['pm_start_date'];  // ควรอยู่ในรูปแบบ YYYY-MM-DD
$pm_end_date = $_POST['pm_end_date'];      // ควรอยู่ในรูปแบบ YYYY-MM-DD
$active = isset($_POST['active']) ? 1 : 0;

// ตรวจสอบความถูกต้องเบื้องต้น เช่น วันที่เริ่มไม่เกินวันที่จบ
if (strtotime($pm_start_date) > strtotime($pm_end_date)) {
    echo "Start date cannot be later than end date.";
    exit;
}

// คำสั่ง SQL อัปเดต
$sql = "UPDATE promotion SET 
            pm_name = '$pm_name',
            description = '$description',
            discount = $discount,
            apply_to_all = $apply_to_all,
            pm_start_date = '$pm_start_date',
            pm_end_date = '$pm_end_date',
            active = $active
        WHERE promotion_id = $promotion_id";

if (mysqli_query($conn, $sql)) {
    header("Location: table_promotion.php");  // เปลี่ยนเป็นหน้าที่แสดงตารางโปรโมชั่นของคุณ
    exit;
} else {
    echo "Error updating promotion: " . mysqli_error($conn);
}
?>
