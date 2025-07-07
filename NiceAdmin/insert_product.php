<?php
require_once "connect_db.php";
$statusMsg = '';

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

// Check if file is selected and move to the target directory
if (!empty($_FILES["file"]["name"])) {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        // Insert file name and product details into the database
        $sql = "INSERT INTO products (name, description, price, brand_id, stock, image) 
                VALUES ('$product_name', '$description', '$price', '$brand_id', '$stock', '$fileName')";

        if (mysqli_query($conn, $sql)) {
            $statusMsg = "The file " . $fileName . " has been uploaded and product details have been inserted.";
        } else {
            $statusMsg = "Database insertion failed: " . mysqli_error($conn);
        }
    } else {
        $statusMsg = "Sorry, there was an error uploading your file.";
    }
} else {
    $statusMsg = 'Please select a file to upload.';
}

// Display status message
echo $statusMsg;
?>
<meta http-equiv="refresh" content="2; url=tables-data-product-admin.php">
