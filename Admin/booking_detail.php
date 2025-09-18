<?php
require_once("connect_db.php");
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking Detail</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background:#fff; }
    .section-title{font-weight:600;margin:.75rem 0 .5rem}
    .summary-table th{background:#f8f9fa;font-weight:600;width:30%}
    .badge-status{font-size:.9rem}
    .evidence-thumb{width:150px;height:150px;overflow:hidden;border:1px solid #dee2e6;border-radius:8px;cursor:pointer;background:#fff}
    .evidence-thumb img{width:100%;height:100%;object-fit:contain}
  </style>
</head>

<body>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body pt-4">

<?php
if (!isset($_GET['id'])) { echo "ไม่พบ ID การจอง"; exit; }
$booking_id = (int)$_GET['id'];

/* ดึงข้อมูล booking + customer + staff */
$sql = "
  SELECT 
    b.booking_id, b.booking_date, b.time_start, b.time_end,
    b.final_price, b.status, b.evidence, b.total_discount, b.discount_detail,
    c.customer_name, c.gmail, c.tel,
    s.staff_name
  FROM booking b
  LEFT JOIN customer c ON b.customer_id = c.customer_id
  LEFT JOIN staff    s ON b.staff_id    = s.staff_id
  WHERE b.booking_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) { echo "ไม่พบข้อมูลการจองนี้"; exit; }

/* ดึงรายการบริการที่จอง */
$service_sql = "
  SELECT sv.service_name, so.duration, so.price
  FROM booking_seviceop bs
  JOIN service_option so ON bs.option_id = so.option_id
  JOIN service sv       ON so.service_id = sv.service_id
  WHERE bs.booking_id = ?
";
$stmt2 = $conn->prepare($service_sql);
$stmt2->bind_param("i", $booking_id);
$stmt2->execute();
$service_result = $stmt2->get_result();
$services = [];
$totalMinutes = 0;
$totalPrice   = 0.0;
while ($row = $service_result->fetch_assoc()) {
  $services[] = $row;
  $totalMinutes += (int)$row['duration'];
  $totalPrice   += (float)$row['price'];
}
$stmt2->close();

/* ฟอร์แมตวัน/เวลาเป็นสตริงไม่ผ่าน timezone */
$dayStr   = htmlspecialchars(substr($booking['booking_date'], 0, 10));
$startStr = htmlspecialchars(substr($booking['time_start'], 0, 5));
$endStr   = htmlspecialchars(substr($booking['time_end'],   0, 5));

/* สถานะ & badge */
$status = trim((string)$booking['status']);
$badgeClass = 'bg-secondary';
if ($status === 'confirmed') $badgeClass = 'bg-success';
elseif ($status === 'pending') $badgeClass = 'bg-warning text-dark';
elseif ($status === 'complate' || $status === 'completed') $badgeClass = 'bg-primary';
elseif ($status === 'cancelled' || $status === 'rejected') $badgeClass = 'bg-danger';

/* ปุ่มอัปเดตสถานะ (จะแสดงในแถว Status ของตารางเท่านั้น) */
$statusActionBtn = '';
if ($status === 'pending') {
  $statusActionBtn = '
    <form action="update_booking_status.php" method="POST" class="d-inline"
          onsubmit="return confirm(\'ยืนยันเปลี่ยนสถานะเป็น Confirmed ?\');">
      <input type="hidden" name="booking_id" value="'.(int)$booking_id.'">
      <input type="hidden" name="new_status" value="confirmed">
      <button type="submit" class="btn btn-success btn-sm">Mark as Confirmed</button>
    </form>
  ';
} elseif ($status === 'confirmed') {
  $statusActionBtn = '
    <form action="update_booking_status.php" method="POST" class="d-inline"
          onsubmit="return confirm(\'ยืนยันเปลี่ยนสถานะเป็น Complate ?\');">
      <input type="hidden" name="booking_id" value="'.(int)$booking_id.'">
      <input type="hidden" name="new_status" value="complate">
      <button type="submit" class="btn btn-primary btn-sm">Mark as Complate</button>
    </form>
  ';
}
?>

  <!-- หัวเรื่อง: ลบ badge สถานะออกแล้ว -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">Booking #<?= htmlspecialchars($booking['booking_id']) ?></h1>
    <div class="d-flex gap-2">
      <a href="booking_update_form.php?id=<?= $booking_id ?>" class="btn btn-outline-primary btn-sm">Edit</a>
      <a href="table_booking.php" class="btn btn-outline-secondary btn-sm">Back</a>
      <!-- <button class="btn btn-outline-dark btn-sm" onclick="window.print()">Print</button> -->
    </div>
  </div>

  <!-- สรุปหลัก -->
  <h5 class="section-title">Booking Overview</h5>
  <table class="table table-bordered summary-table mb-4">
    <tr>
      <th>Customer</th>
      <td>
        <?= htmlspecialchars($booking['customer_name'] ?? '—') ?><br>
        <?php if(!empty($booking['tel'])): ?>โทร. <?= htmlspecialchars($booking['tel']) ?><br><?php endif; ?>
        <?php if(!empty($booking['gmail'])): ?><?= htmlspecialchars($booking['gmail']) ?><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>Date & Time</th>
      <td><?= $dayStr ?>, <?= $startStr ?>–<?= $endStr ?></td>
    </tr>
    <tr>
      <th>Staff</th>
      <td><?= htmlspecialchars($booking['staff_name'] ?? '—') ?></td>
    </tr>
    <tr>
      <th>Total Duration</th>
      <td><?= (int)$totalMinutes ?> mins</td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="badge badge-status <?= $badgeClass ?>">
            <?= htmlspecialchars($status ?: '—') ?>
          </span>
          <?php if (!empty($statusActionBtn)): ?>
            <div class="ms-1"><?= $statusActionBtn ?></div>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- รายการบริการ -->
  <h5 class="section-title">Services</h5>
  <?php if (empty($services)): ?>
    <p class="text-muted">No services found</p>
  <?php else: ?>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>Service</th>
          <th class="text-center" style="width:120px;">Minutes</th>
          <th class="text-end" style="width:140px;">Price (฿)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $srv): ?>
          <tr>
            <td><?= htmlspecialchars($srv['service_name']) ?></td>
            <td class="text-center"><?= (int)$srv['duration'] ?></td>
            <td class="text-end"><?= number_format((float)$srv['price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="fw-semibold">
          <td class="text-end">Total</td>
          <td class="text-center"><?= (int)$totalMinutes ?></td>
          <td class="text-end">฿<?= number_format($totalPrice, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>

  <!-- การชำระเงิน / ส่วนลด / หลักฐาน -->
  <div class="row g-3">
    <div class="col-md-8">
      <h5 class="section-title">Payment & Discount</h5>
      <table class="table table-bordered summary-table mb-4">
        <tr>
          <th>Total Discount</th>
          <td><?= htmlspecialchars((string)$booking['total_discount']) !== '' ? htmlspecialchars($booking['total_discount']) : '—' ?></td>
        </tr>
        <tr>
          <th>Discount Detail</th>
          <td><?= htmlspecialchars($booking['discount_detail'] ?: '—') ?></td>
        </tr>
        <tr>
          <th>Final Price (€)</th>
          <td>€<?= number_format((float)$booking['final_price'], 2) ?></td>
        </tr>
      </table>
    </div>
    <div class="col-md-4">
      <h5 class="section-title">Evidence</h5>
      <?php if (!empty($booking['evidence'])): ?>
        <div class="evidence-thumb" data-bs-toggle="modal" data-bs-target="#imageModal">
          <img src="assets/img/<?= htmlspecialchars($booking['evidence']) ?>" alt="Payment Evidence">
        </div>
        <!-- Modal -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="min-height:300px;">
                <img src="assets/img/<?= htmlspecialchars($booking['evidence']) ?>" alt="Evidence" style="max-width:100%;height:auto;">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a class="btn btn-outline-primary btn-sm" href="assets/img/<?= htmlspecialchars($booking['evidence']) ?>" download>Download</a>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <p class="text-muted">No evidence uploaded.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-3">
    <a href="booking_update_form.php?id=<?= $booking_id ?>" class="btn btn-primary">Edit</a>
    <a href="table_booking.php" class="btn btn-outline-secondary">Back</a>
  </div>

          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section>
</main>

<?php include("footer.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
