
<?php
    require_once("connect_db.php");

    $first_name = $_POST['floatingfName'];
    $last_name = $_POST['floatinglName'];
    $email = $_POST['floatingEmail'];
    $phone = $_POST['floatingPhone'];
    $birthday = $_POST['floatingDate'];
    $gender = $_POST['floatinggender'];
    $address = $_POST['floatingAddress'];
    $password = md5( $_POST['floatingPassword']);


    $sql ="insert into member(mb_first_name, mb_last_name, mb_gmail, mb_tel, mb_birthday, mb_gender, mb_address, mb_password, mb_status) 
    values('$first_name', '$last_name', '$email', '$phone', '$birthday', '$gender', '$address', '$password', 'active')";

    mysqli_query($conn,$sql);
    header("Location: forms_member.php");
exit;
?>
