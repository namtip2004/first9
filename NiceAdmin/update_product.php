<?php
require_once "connect_db.php";
$statusMsg = '';

// รับข้อมูลจากฟอร์ม
$product_id = $_GET['id']; // รับ ID ของสินค้าจาก URL
$product_name = $_POST['floatingProductName'];
$description = $_POST['floatingDescription'];
$brand_id = $_POST['floatingBrand'];
$price = $_POST['floatingPrice'];
$stock = $_POST['floatingStock'];

// Directory to upload file
$targetDir = "assets/img/";
$fileName = basename($_FILES["file"]["name"]);
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

// ตรวจสอบว่ามีไฟล์รูปภาพถูกเลือกหรือไม่
if (!empty($_FILES["file"]["name"])) {
    // อัปโหลดไฟล์และอัปเดตข้อมูลพร้อมรูปภาพ
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        // อัปเดตรวมถึงชื่อรูปภาพใหม่ในฐานข้อมูล
        $sql = "UPDATE products 
                SET name = '$product_name', 
                    description = '$description', 
                    price = '$price', 
                    brand_id = '$brand_id', 
                    stock = '$stock', 
                    image = '$fileName' 
                WHERE id = '$product_id'";

        if (mysqli_query($conn, $sql)) {
            $statusMsg = "The product details have been updated with the new image.";
        } else {
            $statusMsg = "Database update failed: " . mysqli_error($conn);
        }
    } else {
        $statusMsg = "Sorry, there was an error uploading your file.";
    }
} else {
    // อัปเดตข้อมูลโดยไม่เปลี่ยนรูปภาพ
    $sql = "UPDATE products 
            SET name = '$product_name', 
                description = '$description', 
                price = '$price', 
                brand_id = '$brand_id', 
                stock = '$stock' 
            WHERE id = '$product_id'";

    if (mysqli_query($conn, $sql)) {
        $statusMsg = "The product details have been updated.";
    } else {
        $statusMsg = "Database update failed: " . mysqli_error($conn);
    }
}

// แสดงสถานะการอัปเดต
echo $statusMsg;
?>
<meta http-equiv="refresh" content="2; url=tables-data-product-admin.php">
