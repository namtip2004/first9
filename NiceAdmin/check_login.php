<?php
    session_start();    
    require_once "connect_db.php";

    $statusMsg = '';

    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = "select * from customer where email = '" . $username . "' and password = '" . $password . "'";
    $result = mysqli_query($conn,$query);

    $rowcount=mysqli_num_rows($result);

    if($rowcount>0){
        echo "login ถูกต้อง";
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['customer_name'] = $row['customer_name'];
        $_SESSION['customer_id'] = $row['customer_id'];
        //$_SESSION['image'] = $row['image'];
        $_SESSION['level'] = $row['level'];

        if($row['level']==1)
            header('location: index-user.php');

        else if($row['level']==9){
            //header('Location: index-user.php');
            header('Location: index-admin.php');

        }else {
        echo "login ไม่ถูกต้อง";
        /*echo '<script>alert("Login Fail.")</script>';
        header('Location: index.html'); */
         }
        }

    ?>