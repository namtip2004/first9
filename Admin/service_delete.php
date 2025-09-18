<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
    echo "ไม่พบรหัสคอร์สที่ต้องการลบ";
    exit;
}

$service_id = (int) $_GET['id']; // บังคับให้เป็นตัวเลข

$sql = "UPDATE service SET is_active = 0 WHERE service_id = $service_id";
$result = mysqli_query($conn, $sql);

if ($result) {
    // ถ้าอัปเดตสำเร็จ กลับไปหน้ารายการ
    header("Location: table_service.php");
    exit;
} else {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
