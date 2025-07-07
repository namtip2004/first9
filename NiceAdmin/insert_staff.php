<?php
require_once("connect_db.php");

// รับค่าจากฟอร์ม
$first_name   = mysqli_real_escape_string($conn, $_POST['floatingfName']);
$last_name    = mysqli_real_escape_string($conn, $_POST['floatinglName']);
$gender       = mysqli_real_escape_string($conn, $_POST['floatinggender']);
$age          = (int)$_POST['floatingAge'];
$nationality  = mysqli_real_escape_string($conn, $_POST['floatingnation']);
$email        = mysqli_real_escape_string($conn, $_POST['floatingEmail']);
$phone        = mysqli_real_escape_string($conn, $_POST['floatingPhone']);
$address      = mysqli_real_escape_string($conn, $_POST['floatingAddress']);
$start_job    = mysqli_real_escape_string($conn, $_POST['floatingstdate']);

// สถานะเริ่มต้นของพนักงาน
$status = 'active';

// คำสั่ง SQL สำหรับ insert
$sql = "INSERT INTO staff (
            staff_F_name, 
            staff_L_name, 
            staff_gender, 
            staff_age, 
            staff_nationality, 
            staff_mail, 
            staff_tel, 
            staff_address, 
            start_job, 
            staff_status
        ) VALUES (
            '$first_name', 
            '$last_name', 
            '$gender', 
            $age, 
            '$nationality', 
            '$email', 
            '$phone', 
            '$address', 
            '$start_job', 
            '$status'
        )";

// บันทึกข้อมูล
if (mysqli_query($conn, $sql)) {
    header("Location: forms_staff.php"); // กลับไปหน้าฟอร์มหรือรายการ staff
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . mysqli_error($conn);
}
?>
