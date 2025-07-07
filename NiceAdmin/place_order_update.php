<?php
// Include the database configuration file
require_once "connect_db.php"; //อย่าลืมแก้ถ้าตั้งไว้ไม่เหมือน
$statusMsg = '';
echo "<script>alert('update payment status')</script>";

$order_id = $_POST['order_id'];
$pay_date = $_POST['pay_date'];
$pay_time = $_POST['pay_time'];
//$bank = $_POST['bank']; แจ้งว่าโอนมาขากธนาคารไหน

// File upload path
$targetDir = "assets/img/evidence/"; //โฟลเดอร์evidence คือหลักฐานการโอน
$fileName = basename($_FILES["file"]["name"]); //มีเพิ่มในฟอร์มนิดหน่อย
$targetFilePath = $targetDir . $fileName;
$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION); 
//ตรวจสอบ ต้องไม่เป็นที่ว่าง 
if(isset($_POST["submit"]) && !empty($_FILES["file"]["name"])){
    //ตรวจสอบว่าอัปโหลดสำเร็จไหม 
     if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
            // update data in order table
            $query = "update orders set status='1',pay_date='".$pay_date."',
                        pay_time='".$pay_time."',evidence='".$fileName."'";
            $query .=" where order_id = '".$order_id."'"; //ฟรีค่าส่ง
         
            mysqli_query($conn,$query);

            echo $query;
            
                $statusMsg = "The file ".$fileName. " has been uploaded successfully and data has been inserted";
        }else{
            $statusMsg = "Sorry, there was an error uploading your file.";
        }
   
}else{
    $statusMsg = 'Please select a file to upload.';
}

// Display status message
echo $statusMsg;

?>
<meta http-equiv="refresh" content = "0; url = tables-data-orders.php">