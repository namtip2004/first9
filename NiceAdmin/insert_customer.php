
<?php
    require_once("connect_db.php");

    $name = $_POST['floatingName'];
    $gender = $_POST['floatinggender'];
    $birthday = $_POST['floatingDate'];
    $email = $_POST['floatingEmail'];
    $phone = $_POST['floatingPhone'];
    $password = md5( $_POST['floatingPassword']);


    $sql ="insert into customer(customer_name, gender, birthday, gmail, tel, pass, account_status) 
    values('$name', '$gender', '$birthday', '$email', '$phone', '$password', 'active')";

    mysqli_query($conn,$sql);
    header("Location: table_customer.php");
exit;
?>
