<?php
require_once("connect_db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: table_staff.php");
    exit;
}

// รับข้อมูลจากฟอร์ม
$staff_id = intval($_POST['staff_id']);
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// ตรวจสอบข้อมูลที่ส่งมา
if (empty($staff_id) || empty($new_password) || empty($confirm_password)) {
    echo "<script>
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        window.history.back();
    </script>";
    exit;
}

// ตรวจสอบว่ารหัสผ่านตรงกัน
if ($new_password !== $confirm_password) {
    echo "<script>
        alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
        window.history.back();
    </script>";
    exit;
}

// ตรวจสอบความยาวรหัสผ่าน
if (strlen($new_password) < 6) {
    echo "<script>
        alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        window.history.back();
    </script>";
    exit;
}

// ตรวจสอบว่ามีพนักงานคนนี้อยู่จริงหรือไม่
$check_sql = "SELECT staff_id, staff_name FROM staff WHERE staff_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $staff_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo "<script>
        alert('ไม่พบข้อมูลพนักงาน');
        window.location='table_staff.php';
    </script>";
    exit;
}

$staff_data = $check_result->fetch_assoc();

try {
    // เข้ารหัสรหัสผ่านใหม่
    $hashed_password = md5($new_password);
    
    // อัปเดตรหัสผ่านใหม่
    $update_sql = "UPDATE staff SET st_pass = ? WHERE staff_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $hashed_password, $staff_id);
    
    if ($update_stmt->execute()) {
        // บันทึก log การเปลี่ยนรหัสผ่าน (ถ้าต้องการ)
        $log_sql = "INSERT INTO password_reset_log (staff_id, reset_date, reset_by) VALUES (?, NOW(), ?)";
        $log_stmt = $conn->prepare($log_sql);
        $admin_id = $_SESSION['admin_id'] ?? 'system'; // ถ้ามี session ของ admin
        $log_stmt->bind_param("is", $staff_id, $admin_id);
        $log_stmt->execute();
        
        echo "<script>
            alert('รีเซ็ตรหัสผ่านของ " . htmlspecialchars($staff_data['staff_name']) . " เรียบร้อยแล้ว');
            window.location='staff_profile.php?id=" . $staff_id . "';
        </script>";
    } else {
        throw new Exception("ไม่สามารถอัปเดตรหัสผ่านได้");
    }
    
} catch (Exception $e) {
    echo "<script>
        alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "');
        window.history.back();
    </script>";
}
echo "<script>alert('add completed'); window.location='profile.php';</script>";
// ปิดการเชื่อมต่อ
$conn->close();
?>