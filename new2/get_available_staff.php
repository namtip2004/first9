<?php
header('Content-Type: application/json; charset=utf-8');

$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9;charset=utf8mb4","root","");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$date       = $_GET['date'] ?? '';
$start_time = $_GET['start_time'] ?? '';
$duration   = (int)($_GET['duration'] ?? 0);
$end_time   = date("H:i", strtotime("+$duration minutes", strtotime($start_time)));

// ดึง staff ที่ active และไม่ชนเวลาจอง
$stmt = $pdo->prepare("
    SELECT staff_id, staff_name, st_profile
    FROM staff
    WHERE st_status = 'active'
      AND NOT EXISTS (
        SELECT 1 FROM booking b
        WHERE b.staff_id = staff.staff_id
          AND b.booking_date = ?
          AND (b.time_start < ? AND b.time_end > ?)  -- ช่วงเวลาทับซ้อนมาตรฐาน
      )
    ORDER BY staff_name
");
$stmt->execute([$date, $end_time, $start_time]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * ถ้า st_profile เก็บ:
 *  - URL เต็ม (เช่น https://... หรือ /uploads/...) ใช้ได้เลย
 *  - แค่ชื่อไฟล์ (เช่น 12.jpg) ให้เติม base URL ด้านล่าง
 */
$baseUrl = "../admin/assets/img/"; // แก้ให้ตรงโฟลเดอร์รูปจริงของโปรเจกต์คุณ

$out = array_map(function($r) use ($baseUrl) {
    $path = trim($r['st_profile'] ?? '');
    $image_url = $path;
    if ($path !== '') {
        // ถ้าไม่ใช่ http(s) และไม่ได้ขึ้นต้นด้วย '/' ให้ prepend โฟลเดอร์
        if (!preg_match('#^https?://#i', $path) && strpos($path, '/') !== 0) {
            $image_url = $baseUrl . $path;
        }
    } else {
        // รูป fallback
        $image_url = $baseUrl . "default.png";
    }
    return [
        'staff_id'   => (int)$r['staff_id'],
        'staff_name' => $r['staff_name'],
        'image_url'  => $image_url,
    ];
}, $rows);

echo json_encode($out, JSON_UNESCAPED_UNICODE);
