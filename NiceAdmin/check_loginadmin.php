<?php
session_start();
require_once "connect_db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($gmail) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอก gmail และรหัสผ่านให้ครบ";
        header("Location: loginadmin.php");
        exit();
    }

    // เข้ารหัสรหัสผ่านด้วย md5 (ตามฐานข้อมูล)
    // $passwordMd5 = md5($password);

    // ใช้ prepared statement ป้องกัน SQL injection
    $sql = "SELECT * FROM staff WHERE st_gmail = ? AND st_pass = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $gmail, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // // ตรวจสอบสถานะบัญชี
        // if ($row['st_status'] !== 'active') {
        //     $_SESSION['error'] = "บัญชีของคุณถูกระงับการใช้งาน";
        //     header("Location: loginadmin.php");
        //     exit();
        // }

        // ตั้ง session ข้อมูล staff
        $_SESSION['staff_id'] = $row['staff_id'];
        $_SESSION['staff_name'] = $row['staff_name'];
        $_SESSION['staff_gmail'] = $row['st_gmail'];
        $_SESSION['staff_level'] = $row['st_level'] ?? 'staff';
        $_SESSION['st_profile'] = $row['st_profile'];

        // เปลี่ยนหน้า login ตามระดับ st_level
        if ($_SESSION['staff_level'] == 'admin') {
            header("Location: index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "gmail หรือรหัสผ่านไม่ถูกต้อง";
        header("Location: loginadmin.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
