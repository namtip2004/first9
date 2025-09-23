<?php
session_start();
require_once 'connect_db.php';
require_once 'promotion_utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบโปรโมชั่นที่ต้องการสิ้นสุด'));
    exit;
}

$promotionId = isset($_POST['promotion_id']) ? (int) $_POST['promotion_id'] : 0;
if ($promotionId <= 0) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบโปรโมชั่นที่ต้องการสิ้นสุด'));
    exit;
}

$stmt = $conn->prepare('SELECT pm_start_date, pm_end_date FROM promotion WHERE promotion_id = ?');
if (!$stmt) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่สามารถตรวจสอบข้อมูลโปรโมชั่นได้'));
    exit;
}
$stmt->bind_param('i', $promotionId);
$stmt->execute();
$result = $stmt->get_result();
$promotion = $result->fetch_assoc();
$stmt->close();

if (!$promotion) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบโปรโมชั่นที่ต้องการสิ้นสุด'));
    exit;
}

$status = promotionStatus($promotion['pm_start_date'], $promotion['pm_end_date']);
if ($status !== 'running') {
    header('Location: table_promotion.php?error=' . urlencode('สามารถสิ้นสุดได้เฉพาะโปรโมชั่นที่กำลังดำเนินการเท่านั้น'));
    exit;
}

$now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
$columns = getPromotionColumns($conn);
$hasActive = in_array('active', $columns, true);

if ($hasActive) {
    $stmt = $conn->prepare('UPDATE promotion SET pm_end_date = ?, active = 0 WHERE promotion_id = ?');
} else {
    $stmt = $conn->prepare('UPDATE promotion SET pm_end_date = ? WHERE promotion_id = ?');
}

if (!$stmt) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่สามารถสิ้นสุดโปรโมชั่นได้'));
    exit;
}

$stmt->bind_param('si', $now, $promotionId);
$stmt->execute();
$stmt->close();

header('Location: table_promotion.php?message=' . urlencode('สิ้นสุดโปรโมชั่นเรียบร้อยแล้ว'));
exit;
