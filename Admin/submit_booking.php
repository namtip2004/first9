<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable PDO exceptions
$pdo->beginTransaction();

try {
    $customer_id = $_POST['customer_id'];; // Example: Hardcoded for demo; replace with authenticated user ID
    $staff_id = $_POST['staff_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $services = $_POST['services'];
    $options = $_POST['options'];
    $targetDir = "assets/img/";

    // File upload handling
    $evidence_image = "no_image.jpg"; // Default value for evidence column
    if (isset($_FILES["imgprofile"]) && $_FILES["imgprofile"]["error"] == 0) {
        $fileName = basename($_FILES["imgprofile"]["name"]);
        $targetFilePath = $targetDir . uniqid() . '_' . $fileName; // Add uniqid to prevent file name conflicts
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate file type and size (e.g., allow only images, max 5MB)
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        if (in_array($fileType, $allowedTypes) && $_FILES["imgprofile"]["size"] <= $maxFileSize) {
            if (move_uploaded_file($_FILES["imgprofile"]["tmp_name"], $targetFilePath)) {
                $evidence_image = basename($targetFilePath); // Store only the file name
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            throw new Exception("Invalid file type or size. Only JPG, JPEG, PNG, GIF files under 5MB are allowed.");
        }
    }

    // Calculate total duration and prices
    $total_duration = 0;
    $total_price = 0;
    $total_discount = 0;
   $discount_name = null;

    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT duration, price FROM service_option WHERE option_id = ?");
        $stmt->execute([$option_id]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$option) {
            throw new Exception("Invalid option_id: $option_id");
        }
        $total_duration += $option['duration'];
        $total_price += $option['price'];

    }

    $end_time = date("H:i", strtotime("+$total_duration minutes", strtotime($start_time)));
    $final_price = $total_price - $total_discount;

    // Insert into booking with evidence
    $stmt = $pdo->prepare("
        INSERT INTO booking (customer_id, staff_id, booking_date, time_start, time_end, total_price, total_discount, final_price, status, discount_detail, evidence)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, ?)
    ");
    $stmt->execute([$customer_id, $staff_id, $booking_date, $start_time, $end_time, $total_price, $total_discount, $final_price, $discount_name, $evidence_image]);
    $booking_id = $pdo->lastInsertId();

    // Insert into booking_seviceop
    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT price FROM service_option WHERE option_id = ?");
        $stmt->execute([$option_id]);
        $price = $stmt->fetchColumn();
        if ($price === false) {
            throw new Exception("Invalid option_id: $option_id");
        }
        $discount = 0;

        $net_price = $price - $discount;
        $stmt = $pdo->prepare("INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $option_id, $price, $discount, $net_price]);
    }

    $pdo->commit();
    header("Location: table_booking.php"); // Redirect to table_booking.php
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>