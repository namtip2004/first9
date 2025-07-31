<?php
require_once("connect_db.php");

// รับค่า staff_ID จาก URL
$staff_id = (int)$_GET['id'];

// รับค่าจากฟอร์ม
$name   = mysqli_real_escape_string($conn, $_POST['floatingName']);
$gender     = mysqli_real_escape_string($conn, $_POST['floatinggender']);
$age        = (int)$_POST['floatingAge'];
$birth      = $_POST['birthday'];
$email      = mysqli_real_escape_string($conn, $_POST['floatingEmail']);
$phone      = mysqli_real_escape_string($conn, $_POST['floatingPhone']);
$address    = mysqli_real_escape_string($conn, $_POST['floatingAddress']);
$start_job  = mysqli_real_escape_string($conn, $_POST['floatingstdate']);
$end_job    = mysqli_real_escape_string($conn, $_POST['floatingendate']);
$status     = mysqli_real_escape_string($conn, $_POST['floatingStatus']);

// คำสั่ง SQL สำหรับอัปเดต
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
            st_status = '$status'
        WHERE staff_id = $staff_id";

// ประมวลผล
if (mysqli_query($conn, $sql)) {
    // กลับไปหน้าตารางพนักงาน
    header("Location: table_staff.php");
    exit;
} else {
    echo "error: " . mysqli_error($conn);
}
?>
