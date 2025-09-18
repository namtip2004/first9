<?php
session_start();
require_once("connect_db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag_id = (int)($_POST['tag_id'] ?? 0);
    $tag_name = trim($_POST['tag_name'] ?? '');
    $services = $_POST['services'] ?? [];

    if ($tag_id > 0 && $tag_name !== '' && is_array($services) && count($services) > 0) {
        mysqli_begin_transaction($conn);

        try {
            // อัพเดตชื่อ tag
            $stmt = $conn->prepare("UPDATE tag SET tag_name = ? WHERE tag_id = ?");
            $stmt->bind_param("si", $tag_name, $tag_id);
            $stmt->execute();
            $stmt->close();

            // ลบ tag_service เก่าทั้งหมดของ tag นี้
            $stmt = $conn->prepare("DELETE FROM tag_service WHERE tag_id = ?");
            $stmt->bind_param("i", $tag_id);
            $stmt->execute();
            $stmt->close();

            // เพิ่ม tag_service ใหม่
            $stmt = $conn->prepare("INSERT INTO tag_service (tag_id, service_id) VALUES (?, ?)");
            foreach ($services as $service_id) {
                $service_id = (int)$service_id;
                $stmt->bind_param("ii", $tag_id, $service_id);
                $stmt->execute();
            }
            $stmt->close();

            mysqli_commit($conn);

            $_SESSION['success'] = "Tag updated successfully.";
            header("Location: table_tags.php");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = "Update failed: " . $e->getMessage();
            header("Location: tag_update_form.php?id=" . $tag_id);
            exit;
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: tag_update_form.php?id=" . $tag_id);
        exit;
    }
} else {
    header("Location: table_tags.php");
    exit;
}
?>
