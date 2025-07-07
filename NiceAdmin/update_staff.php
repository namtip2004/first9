<?php
require_once("connect_db.php");

// รับค่า staff_ID จาก URL
$staff_id = (int)$_GET['id'];

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
$end_job    = mysqli_real_escape_string($conn, $_POST['floatingendate']);
$status       = mysqli_real_escape_string($conn, $_POST['floatingStatus']);

// คำสั่ง SQL สำหรับอัปเดต
$sql = "UPDATE staff SET 
            staff_F_name = '$first_name',
            staff_L_name = '$last_name',
            staff_gender = '$gender',
            staff_age = $age,
            staff_nationality = '$nationality',
            staff_mail = '$email',
            staff_tel = '$phone',
            staff_address = '$address',
            start_job = '$start_job',
            end_job = '$end_job',
            staff_status = '$status'
        WHERE staff_ID = $staff_id";

// ประมวลผล
if (mysqli_query($conn, $sql)) {
    // กลับไปหน้าตารางพนักงาน
    header("Location: tables_staff.php");
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
}
?>
