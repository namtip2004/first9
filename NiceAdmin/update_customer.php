<?php
require_once("connect_db.php");

$customer_id = $_POST['customer_id'];
$name = $_POST['customer_name'];
$gender = $_POST['gender'];
$birth = $_POST['birthday'];
$email = $_POST['gmail'];
$tel = $_POST['tel'];

$targetDir = "assets/img/";
$updateProfileImg = ""; // สำหรับ SQL update

// เช็คว่ามีการอัปโหลดไฟล์ใหม่ไหม
if (isset($_FILES["profileimg"]) && $_FILES["profileimg"]["error"] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES["profileimg"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["profileimg"]["tmp_name"], $targetFilePath)) {
        $updateProfileImg = ", profileimg = '$fileName'";
    } else {
        die("Error uploading new profile image.");
    }
}

// อัปเดตข้อมูลลูกค้า
$sql = "UPDATE customer SET 
          customer_name = '$name',
          gender = '$gender',
          birthday = '$birth',
          gmail = '$email',
          tel = '$tel'
          $updateProfileImg
        WHERE customer_id = '$customer_id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Customer updated successfully'); window.location='table_customer.php';</script>";
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
