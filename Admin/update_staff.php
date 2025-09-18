<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
    echo "ไม่พบ ID";
    exit;
}

$staff_id = intval($_GET['id']);

// รับค่าจากฟอร์ม
$name      = mysqli_real_escape_string($conn, $_POST['staff_name']);
$gender    = mysqli_real_escape_string($conn, $_POST['st_gender']);
$age       = intval($_POST['st_age']);
$birth     = $_POST['st_birthday'];
$email     = mysqli_real_escape_string($conn, $_POST['st_gmail']);
$phone     = mysqli_real_escape_string($conn, $_POST['st_tel']);
$address   = mysqli_real_escape_string($conn, $_POST['st_address']);
$start_job = $_POST['start_job'];
$end_job   = $_POST['end_job'];
$status    = mysqli_real_escape_string($conn, $_POST['st_status']);
$old_image = $_POST['old_image'];

$targetDir = "assets/img/";
$newImageName = "";

if (isset($_FILES['st_profile']) && $_FILES['st_profile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['st_profile']['tmp_name'];
    $fileName = basename($_FILES['st_profile']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ (เช่น staffID_timestamp.นามสกุล)
    $newImageName = $staff_id . "_" . time() . "." . $fileExtension;
    $destPath = $targetDir . $newImageName;

    // อัพโหลดไฟล์
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // ลบไฟล์รูปเก่า ถ้ามี และต่างจากรูปใหม่
        if (!empty($old_image) && file_exists($targetDir . $old_image)) {
            unlink($targetDir . $old_image);
        }
    } else {
        die("เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ");
    }
} else {
    // ไม่มีการอัปโหลดรูปใหม่ ให้ใช้รูปเก่า
    $newImageName = $old_image;
}

// อัปเดตข้อมูลในฐานข้อมูล พร้อมรูปภาพ
$sql = "UPDATE staff SET 
            staff_name = '$name',
            st_gender = '$gender',
            st_age = $age,
            st_birthday = '$birth',
            st_gmail = '$email',
            st_tel = '$phone',
            st_address = '$address',
            start_job = '$start_job',
            end_job = '$end_job',
            st_status = '$status',
            st_profile = '$newImageName'
        WHERE staff_id = $staff_id";

if (mysqli_query($conn, $sql)) {
    header("Location: table_staff.php");
    exit;
} else {
    echo "Error updating record: " . mysqli_error($conn);
}
?>
