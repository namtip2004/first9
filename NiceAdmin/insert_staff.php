<?php
require_once("connect_db.php");

// รับค่าจากฟอร์ม
$name   = mysqli_real_escape_string($conn, $_POST['floatingName']);
$gender       = mysqli_real_escape_string($conn, $_POST['floatinggender']);
$age          = (int)$_POST['floatingAge'];
$birthday = $_POST['floatingDate'];
$email        = mysqli_real_escape_string($conn, $_POST['floatingGmail']);
$phone        = mysqli_real_escape_string($conn, $_POST['floatingPhone']);
$address      = mysqli_real_escape_string($conn, $_POST['floatingAddress']);
$password = md5( $_POST['floatingPassword']);
$start_job    = mysqli_real_escape_string($conn, $_POST['floatingstdate']);

// สถานะเริ่มต้นของพนักงาน
$status = 'active';
$role = 'staff';

// คำสั่ง SQL สำหรับ insert
$sql = "INSERT INTO staff (
            staff_name, 
            st_gender, 
            st_age,
            st_birthday, 
            st_gmail, 
            st_tel, 
            st_address,
            st_pass, 
            start_job, 
            st_status,
            st_level
        ) VALUES (
            '$name', 
            '$gender', 
            '$age',
            '$birthday',  
            '$email', 
            '$phone', 
            '$address',
            '$password', 
            '$start_job', 
            '$status',
            '$role'
        )";

// บันทึกข้อมูล
if (mysqli_query($conn, $sql)) {
    header("Location: table_staff.php"); // กลับไปหน้ารายการ staff
    exit;
} else {
    echo "error: " . mysqli_error($conn);
}
?>
