<?php
    require_once("connect_db.php");

    $product_name = $_POST['floatingProductName'];
    $description = $_POST['floatingDescription'];
    $brand_id = $_POST['floatingBrand'];
    $price = $_POST['floatingPrice'];
    $stock = $_POST['floatingStock'];
    
    $image = basename($_FILES["image"]["name"]);

    // File upload path
    $targetDir = "assets/img/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Check if form is submitted and file is selected
    if (isset($_POST["submit"]) && !empty($_FILES["image"]["name"])) {

        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            // Insert data into database
            $sql = "INSERT INTO products (name, description, price, brand_id, stock, image) ";
            $sql .= "VALUES ('$product_name', '$description', '$price', '$brand_id', '$stock', '$fileName')";
            if (mysqli_query($conn, $sql)) {
                $statusMsg = "The file " . $fileName . " has been uploaded successfully and data has been inserted.";
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
