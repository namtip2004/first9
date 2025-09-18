<?php
require_once("connect_db.php");

$name = $_POST['floatingName'] ?? '';
$gender = $_POST['floatinggender'] ?? '';
$birthday = $_POST['floatingDate'] ?? '';
$email = $_POST['floatingEmail'] ?? '';
$phone = $_POST['floatingPhone'] ?? '';
$password = md5($_POST['floatingPassword'] ?? '');

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
        // บันทึกข้อมูลลูกค้า พร้อมชื่อไฟล์รูปภาพ
        $stmt = $conn->prepare("INSERT INTO customer (customer_name, gender, birthday, gmail, tel, pass, profileimg, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssssss", $name, $gender, $birthday, $email, $phone, $password, $fileName);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Customer added successfully'); window.location='table_customer.php';</script>";
        exit;
    } else {
        die("Error uploading file.");
    }
} else {
    die("Please select a file to upload.");
}
?>


