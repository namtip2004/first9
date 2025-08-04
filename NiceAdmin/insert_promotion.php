<?php
require_once("connect_db.php");

// Get data from form
$pm_name       = mysqli_real_escape_string($conn, $_POST['pm_name']);
$description   = mysqli_real_escape_string($conn, $_POST['description']);
$discount      = (float)$_POST['discount'];
$apply_to_all  = (int)$_POST['apply_to_all'];
$pm_start_date = $_POST['pm_start_date'];
$pm_end_date   = $_POST['pm_end_date'];
$active        = (int)$_POST['active'];
$created_at    = date("Y-m-d H:i:s");

// Insert into promotion table
$sql = "INSERT INTO promotion (pm_name, description, discount, apply_to_all, pm_start_date, pm_end_date, active, pm_created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdissis", $pm_name, $description, $discount, $apply_to_all, $pm_start_date, $pm_end_date, $active, $created_at);

if ($stmt->execute()) {
    $promotion_id = $stmt->insert_id;

    // If apply_to_all = 0, insert selected services into promotion_service
    if ($apply_to_all === 0 && !empty($_POST['service_ids'])) {
        $service_ids = $_POST['service_ids'];

        $psql = "INSERT INTO promotion_service (service_id, promotion_id) VALUES (?, ?)";
        $pstmt = $conn->prepare($psql);

        foreach ($service_ids as $service_id) {
            $sid = (int)$service_id;
            $pstmt->bind_param("ii", $sid, $promotion_id);
            $pstmt->execute();
        }
    }

    // Redirect to promotion table page
    header("Location: table_promotion.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
