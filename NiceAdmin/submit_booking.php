<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
$pdo->beginTransaction();

try {
    $customer_id = 2; // Example: Hardcoded for demo; replace with authenticated user ID
    $staff_id = $_POST['staff_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $services = $_POST['services'];
    $options = $_POST['options'];
    $promotion_id = $_POST['promotion_id'] ?: null;

    // Calculate total duration and prices
    $total_duration = 0;
    $total_price = 0;
    $total_discount = 0;

    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT duration, price FROM service_option WHERE option_id = ?");
        $stmt->execute([$option_id]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_duration += $option['duration'];
        $total_price += $option['price'];

        if ($promotion_id) {
            $stmt = $pdo->prepare("SELECT discount, apply_to_all FROM promotion WHERE promotion_id = ?");
            $stmt->execute([$promotion_id]);
            $promo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($promo['apply_to_all'] || $pdo->query("SELECT COUNT(*) FROM promotion_service WHERE promotion_id = {$promotion_id} AND service_id = {$services[$index]}")->fetchColumn()) {
                $total_discount += $option['price'] * ($promo['discount'] / 100);
            }
        }
    }

    $end_time = date("H:i", strtotime("+$total_duration minutes", strtotime($start_time)));
    $final_price = $total_price - $total_discount;

    // Insert into booking
    $stmt = $pdo->prepare("
        INSERT INTO booking (customer_id, staff_id, booking_date, time_start, time_end, total_price, total_discount, final_price, status, discount_detail)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)
    ");
    $stmt->execute([$customer_id, $staff_id, $booking_date, $start_time, $end_time, $total_price, $total_discount, $final_price, $promotion_id ? "Promotion ID: $promotion_id" : null]);
    $booking_id = $pdo->lastInsertId();

    // Insert into booking_seviceop
    foreach ($options as $index => $option_id) {
        $stmt = $pdo->prepare("SELECT price FROM service_option WHERE option_id = ?");
        $stmt->execute([$option_id]);
        $price = $stmt->fetchColumn();
        $discount = 0;
        if ($promotion_id) {
            $stmt = $pdo->prepare("SELECT discount, apply_to_all FROM promotion WHERE promotion_id = ?");
            $stmt->execute([$promotion_id]);
            $promo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($promo['apply_to_all'] || $pdo->query("SELECT COUNT(*) FROM promotion_service WHERE promotion_id = {$promotion_id} AND service_id = {$services[$index]}")->fetchColumn()) {
                $discount = $price * ($promo['discount'] / 100);
            }
        }
        $net_price = $price - $discount;
        $stmt = $pdo->prepare("INSERT INTO booking_seviceop (booking_id, option_id, price_booking, discount_booking, net_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $option_id, $price, $discount, $net_price]);
    }

    $pdo->commit();
    echo "Booking confirmed!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>