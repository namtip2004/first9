<?php
session_start();
require_once "connect_db.php";

// รับค่า email และ password
$email = $_POST['username'];
$password = md5($_POST['password']); // ⚠️ ในระบบจริงควรใช้ password_hash()

// ค้นหาจากอีเมล
$query = "SELECT * FROM customer WHERE gmail = '$email' AND pass= '$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // ตั้งค่าข้อมูล session
    $_SESSION['email'] = $row['gmail']; // เก็บ Gmail
    $_SESSION['customer_name'] = $row['customer_name'];
    $_SESSION['customer_id'] = $row['customer_id'];
    $_SESSION['profileimg'] = $row['profileimg'];

    // ไม่เช็ค level แล้ว เข้าหน้า index-user.php ทุกคน
    header('Location: index.php');
} else {
    echo "<script>alert('อีเมลหรือรหัสผ่านไม่ถูกต้อง'); window.location.href='login.php';</script>";
}
?>
