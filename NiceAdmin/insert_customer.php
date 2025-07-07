<?php
session_start();
?>
<?php
    require_once("connect_db.php");
    
    $custName = $_POST['floatingName'];
    $email = $_POST['floatingEmail'];
    $pwd = md5( $_POST['floatingPassword']);
    $address = $_POST['floatingAddress'];
    $city = $_POST['floatingCity'];
    $province = $_POST['floatingProince'];
    $zip = $_POST['floatingZip'];

    $sql ="insert into 
    customer(customer_name, email, password, address, amphur_id, province_id, zip) 
    values('$custName', '$email', '$pwd', '$address', '$city', '$province', '$zip'  )";

    mysqli_query($conn,$sql);
?>
<meta http-equiv="refresh" content = "0; url =pages-login.php">