<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking Table</title>
  <!-- ถ้ามี CSS เช่น Bootstrap หรือ custom.css ให้ใส่ตรงนี้ -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<?php
require_once("connect_db.php");
include("header.php");
include("slidebar.php");
?>
<main id="main" class="main pt-4">
  <div class="pagetitle">
  <h1>Booking Table</h1>
        <nav>
        <ol class="breadcrumb"></ol>
      </nav>
</div>


    <section class="section">
      <div class="row">
        <div class="col-lg-12">        
          <div class="card">
            <div class="card-body">

<div class="text-end mb-2">
  <a href="booking.php" class="btn btn-success">+ Add Booking</a>
</div>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>No.</th>
      <th>Booking Date</th>
      <th>Start - End</th>
      <th>Customer</th>
      <th>Staff</th>
      <th>Service(s)</th>
      <th>Final Price (€)</th>
      <th>Status</th>
      <th>Detail</th>
      <th>Edit</th>
      <th>Cancel</th>
    </tr>
  </thead>
  <tbody>
    <?php

$sql = "
    SELECT 
        b.booking_id,
        b.b_updated_at AS booking_date,
        b.time_start,
        b.time_end,
        b.final_price,
        b.status,
        c.customer_name,
        s.staff_name
    FROM booking b
    LEFT JOIN customer c ON b.customer_id = c.customer_id
    LEFT JOIN staff s ON b.staff_id = s.staff_id
    ORDER BY b.b_updated_at DESC
";


    $result = mysqli_query($conn, $sql);
    $i = 1;

    while ($row = mysqli_fetch_assoc($result)) {
        $booking_id = $row['booking_id'];

        // Fetch service details
        $service_sql = "
          SELECT sv.service_name, so.duration 
          FROM booking_seviceop bs
          JOIN service_option so ON bs.option_id = so.option_id
          JOIN service sv ON so.service_id = sv.service_id
          WHERE bs.booking_id = $booking_id
        ";
        $service_result = mysqli_query($conn, $service_sql);
        $services = [];

        while ($srv = mysqli_fetch_assoc($service_result)) {
            $services[] = $srv['service_name'] . " ({$srv['duration']} mins)";
        }

        echo "<tr>
            <td>" . $i++ . "</td>
            <td>{$row['booking_date']}</td>
            <td>{$row['time_start']} - {$row['time_end']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['staff_name']}</td>
            <td>" . implode("<br>", $services) . "</td>
            <td>€" . number_format($row['final_price'], 2) . "</td>
            <td>{$row['status']}</td>
            <td><a class='btn btn-outline-primary btn-sm' href='booking_detail.php?id={$row['booking_id']}'>Detail</a></td>
            <td><a class='btn btn-outline-warning btn-sm' href='booking_update_form.php?id={$row['booking_id']}'>Edit</a></td>
            <td><a class='btn btn-outline-danger btn-sm' href='booking_delete.php?id={$row['booking_id']}' onclick=\"return confirm('Are you sure to cancel this booking?');\">Cancel</a></td>
          </tr>";
    }
    ?>
  </tbody>
</table>

<!-- Bootstrap JS ถ้าต้องใช้ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
</div>
</div>
</div>
</div>
</section>

</main>
