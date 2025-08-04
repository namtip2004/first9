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
$password = md5($_POST['floatingPassword']);
$start_job    = mysqli_real_escape_string($conn, $_POST['floatingstdate']);

// สถานะเริ่มต้นของพนักงาน
$status = 'active';
$role = 'staff';

// อัปโหลดรูปภาพ
$targetDir = "assets/img/";
$fileName = basename($_FILES["imgprofile"]["name"] ?? '');
$targetFilePath = $targetDir . $fileName;
$fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

// อนุญาตนามสกุลไฟล์
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

if (isset($_FILES["imgprofile"]) && $_FILES["imgprofile"]["error"] === UPLOAD_ERR_OK) {
    if (!in_array($fileType, $allowedTypes)) {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    if (move_uploaded_file($_FILES["imgprofile"]["tmp_name"], $targetFilePath)) {
        // บันทึกข้อมูลพนักงาน พร้อมชื่อไฟล์รูปภาพ
        $stmt = $conn->prepare("INSERT INTO staff (
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
            st_level,
            st_profile
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssisssssssss", $name, $gender, $age, $birthday, $email, $phone, $address, $password, $start_job, $status, $role, $fileName);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Staff added successfully'); window.location='table_staff.php';</script>";
        exit;
    } else {
        die("Error uploading file.");
    }
} else {
    die("Please select a file to upload.");
}
?>
