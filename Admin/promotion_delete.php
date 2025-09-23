<?php
session_start();
require_once 'connect_db.php';
require_once 'promotion_utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบข้อมูลโปรโมชั่นที่ต้องการลบ'));
    exit;
}

$promotionId = isset($_POST['promotion_id']) ? (int) $_POST['promotion_id'] : 0;
if ($promotionId <= 0) {
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบข้อมูลโปรโมชั่นที่ต้องการลบ'));
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
    header('Location: table_promotion.php?error=' . urlencode('ไม่พบข้อมูลโปรโมชั่นที่ต้องการลบ'));
    exit;
}

$status = promotionStatus($promotion['pm_start_date'], $promotion['pm_end_date']);
if ($status !== 'upcoming') {
    header('Location: table_promotion.php?error=' . urlencode('สามารถลบได้เฉพาะโปรโมชั่นที่ยังไม่เริ่มเท่านั้น'));
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare('DELETE FROM promotion_service_option WHERE promotion_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $promotionId);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare('DELETE FROM promotion_service WHERE promotion_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $promotionId);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare('DELETE FROM promotion WHERE promotion_id = ?');
    if (!$stmt) {
        throw new RuntimeException('ไม่สามารถลบโปรโมชั่นได้');
    }
    $stmt->bind_param('i', $promotionId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header('Location: table_promotion.php?message=' . urlencode('ลบโปรโมชั่นเรียบร้อยแล้ว'));
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    header('Location: table_promotion.php?error=' . urlencode('เกิดข้อผิดพลาดในการลบโปรโมชั่น'));
    exit;
}
