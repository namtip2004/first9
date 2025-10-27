<?php
require_once("connect_db.php");

function safe($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$confirmedStatus = BOOKING_STATUS_CONFIRMED;
$completedStatus  = BOOKING_STATUS_COMPLATE;

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    echo "ไม่พบ Customer ID";
    exit;
}

$customer_id = (int)$_GET['id'];

// ดึงข้อมูลลูกค้า
$sql_customer = "SELECT customer_name FROM customer WHERE customer_id = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param("i", $customer_id);
$stmt_customer->execute();
$customer_result = $stmt_customer->get_result();
$customer = $customer_result->fetch_assoc();
$stmt_customer->close();

if (!$customer) {
    echo "ไม่พบข้อมูลลูกค้า";
    exit;
}

$statusFilter = $_GET['status'] ?? 'all';
$period       = $_GET['period'] ?? 'all';
$dayValue     = $_GET['day'] ?? '';
$monthValue   = $_GET['month_value'] ?? '';
$yearValue    = $_GET['year_value'] ?? '';
$sort         = $_GET['sort'] ?? 'created_at';
$dir          = strtoupper($_GET['dir'] ?? 'DESC');
$dir          = $dir === 'ASC' ? 'ASC' : 'DESC';

// ดึงสถิติการ์ด
$stats_sql = "SELECT
    COUNT(*) AS total_bookings,
    COALESCE(SUM(final_price), 0) AS total_spent,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS confirmed_bookings,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS completed_bookings
  FROM booking
  WHERE customer_id = ?";
$stmt_stats = $conn->prepare($stats_sql);
$stmt_stats->bind_param("iii", $confirmedStatus, $completedStatus, $customer_id);
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result();
$stats = $stats_result->fetch_assoc() ?: [
    'total_bookings'     => 0,
    'total_spent'        => 0,
    'confirmed_bookings' => 0,
    'completed_bookings' => 0,
];
$stmt_stats->close();

// ตัวกรองการค้นหา
$whereParts = ["b.customer_id = ?"];
$types      = "i";
$params     = [$customer_id];

if ($statusFilter !== 'all' && $statusFilter !== '') {
    $statusCode = booking_status_code($statusFilter);
    if ($statusCode !== null) {
        $whereParts[] = "b.status = ?";
        $types       .= "i";
        $params[]     = $statusCode;
    }
}

if ($period === 'day') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $dayValue);
    if ($dateObj) {
        $whereParts[] = "DATE(b.booking_date) = ?";
        $types       .= "s";
        $params[]     = $dateObj->format('Y-m-d');
        $dayValue      = $dateObj->format('Y-m-d');
    } else {
        $dayValue = '';
    }
} elseif ($period === 'month') {
    if (preg_match('/^\\d{4}-\\d{2}$/', $monthValue)) {
        [$y, $m] = explode('-', $monthValue);
        $whereParts[] = "YEAR(b.booking_date) = ?";
        $types       .= "i";
        $params[]     = (int)$y;
        $whereParts[] = "MONTH(b.booking_date) = ?";
        $types       .= "i";
        $params[]     = (int)$m;
    } else {
        $monthValue = '';
    }
} elseif ($period === 'year') {
    if (preg_match('/^\\d{4}$/', $yearValue)) {
        $whereParts[] = "YEAR(b.booking_date) = ?";
        $types       .= "i";
        $params[]     = (int)$yearValue;
    } else {
        $yearValue = '';
    }
}

$orderMap = [
    'created_at'   => "b.b_created_at $dir",
    'service_time' => "b.booking_date $dir, b.time_start $dir"
];
$orderBy = $orderMap[$sort] ?? $orderMap['created_at'];

$sql_bookings = "SELECT
    b.booking_id,
    b.booking_date,
    b.time_start,
    b.time_end,
    b.total_price,
    b.total_discount,
    b.final_price,
    b.status,
    b.b_created_at,
    s.staff_name
  FROM booking b
  LEFT JOIN staff s ON b.staff_id = s.staff_id
  WHERE " . implode(' AND ', $whereParts) . "
  ORDER BY $orderBy";

$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param($types, ...$params);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();
$bookings = [];
while ($row = $result_bookings->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt_bookings->close();

$booking_services = [];
if (!empty($bookings)) {
    $ids = array_column($bookings, 'booking_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql_services = "SELECT
        bs.booking_id,
        s.service_name,
        bs.price_booking,
        bs.discount_booking,
        bs.net_price
      FROM booking_seviceop bs
      LEFT JOIN service_option so ON bs.option_id = so.option_id
      LEFT JOIN service s ON so.service_id = s.service_id
      WHERE bs.booking_id IN ($placeholders)";
    $stmt_services = $conn->prepare($sql_services);
    $types_services = str_repeat('i', count($ids));
    $stmt_services->bind_param($types_services, ...$ids);
    $stmt_services->execute();
    $result_services = $stmt_services->get_result();
    while ($row = $result_services->fetch_assoc()) {
        $booking_services[$row['booking_id']][] = $row;
    }
    $stmt_services->close();
}

$statusOptions = [
    'all'      => 'ทั้งหมด',
    'pending'  => 'Pending',
    'confirmed'=> 'Confirmed',
    'complate' => 'Completed',
    'cancelled'=> 'Cancelled'
];

?>
<!DOCTYPE html>
<html lang="th">
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">
  <div class="pagetitle">
    <h1>Bookings for <?= safe($customer['customer_name']) ?></h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="report_customer.php">Customers Report</a></li>
        <li class="breadcrumb-item active">Customer Bookings</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row g-3">
      <div class="col-xxl-3 col-md-6">
        <div class="card info-card sales-card">
          <div class="card-body">
            <h5 class="card-title">Total Book</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-calendar-check"></i>
              </div>
              <div class="ps-3">
                <h6><?= number_format((int)($stats['total_bookings'] ?? 0)) ?></h6>
                <span class="text-muted small">จำนวนการจองทั้งหมด</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xxl-3 col-md-6">
        <div class="card info-card revenue-card">
          <div class="card-body">
            <h5 class="card-title">Total Spent</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-currency-baht"></i>
              </div>
              <div class="ps-3">
                <h6>€<?= number_format((float)($stats['total_spent'] ?? 0), 2) ?></h6>
                <span class="text-muted small">ยอดใช้จ่ายทั้งหมด</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xxl-3 col-md-6">
        <div class="card info-card customers-card">
          <div class="card-body">
            <h5 class="card-title">Booking Confirmed</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="ps-3">
                <h6><?= number_format((int)($stats['confirmed_bookings'] ?? 0)) ?></h6>
                <span class="text-muted small">ยืนยันแล้ว</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xxl-3 col-md-6">
        <div class="card info-card">
          <div class="card-body">
            <h5 class="card-title">Completed</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-clipboard-check"></i>
              </div>
              <div class="ps-3">
                <h6><?= number_format((int)($stats['completed_bookings'] ?? 0)) ?></h6>
                <span class="text-muted small">เสร็จสมบูรณ์</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
              <h5 class="card-title mb-0">รายละเอียดการจอง</h5>
              <span class="badge bg-primary">ทั้งหมด <?= number_format(count($bookings)) ?> รายการ</span>
            </div>

            <form class="row g-3 align-items-end mt-2" method="get">
              <input type="hidden" name="id" value="<?= (int)$customer_id ?>">
              <div class="col-md-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                  <?php foreach ($statusOptions as $value => $label): ?>
                  <option value="<?= safe($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= safe($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">ช่วงเวลา</label>
                <select class="form-select" name="period" id="periodSelect">
                  <option value="all" <?= $period==='all'?'selected':'' ?>>ทั้งหมด</option>
                  <option value="day" <?= $period==='day'?'selected':'' ?>>รายวัน</option>
                  <option value="month" <?= $period==='month'?'selected':'' ?>>รายเดือน</option>
                  <option value="year" <?= $period==='year'?'selected':'' ?>>รายปี</option>
                </select>
              </div>
              <div class="col-md-3 period-input <?= $period==='day'?'':'d-none' ?>" id="period-day">
                <label class="form-label">เลือกวันที่</label>
                <input type="date" class="form-control" name="day" value="<?= safe($dayValue) ?>">
              </div>
              <div class="col-md-3 period-input <?= $period==='month'?'':'d-none' ?>" id="period-month">
                <label class="form-label">เลือกเดือน</label>
                <input type="month" class="form-control" name="month_value" value="<?= safe($monthValue) ?>">
              </div>
              <div class="col-md-3 period-input <?= $period==='year'?'':'d-none' ?>" id="period-year">
                <label class="form-label">เลือกปี</label>
                <input type="number" class="form-control" name="year_value" value="<?= safe($yearValue) ?>" min="2000" max="2100">
              </div>
              <div class="col-md-3">
                <label class="form-label">Sort by</label>
                <select class="form-select" name="sort">
                  <option value="created_at" <?= $sort==='created_at'?'selected':'' ?>>วันเวลาที่ทำการจอง</option>
                  <option value="service_time" <?= $sort==='service_time'?'selected':'' ?>>วันเวลาที่เข้าใช้บริการ</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">ลำดับ</label>
                <select class="form-select" name="dir">
                  <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>ใหม่-เก่า</option>
                  <option value="ASC" <?= $dir==='ASC'?'selected':'' ?>>เก่า-ใหม่</option>
                </select>
              </div>
              <div class="col-md-auto">
                <button class="btn btn-primary"><i class="bi bi-filter"></i> ใช้ตัวกรอง</button>
              </div>
            </form>

            <div class="table-responsive mt-4">
              <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>วันเวลาที่ทำการจอง</th>
                    <th>วันเวลาที่เข้าใช้บริการ</th>
                    <th>Service</th>
                    <th>Staff</th>
                    <th class="text-end">ราคาก่อนหักส่วนลด</th>
                    <th class="text-end">ส่วนลด</th>
                    <th class="text-end">ราคาหลังหักส่วนลด</th>
                    <th>สถานะ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($bookings)): ?>
                  <tr>
                    <td colspan="9" class="text-center text-muted">ไม่พบการจอง</td>
                  </tr>
                  <?php else: foreach ($bookings as $booking):
                    $createdAt = $booking['b_created_at'] ? date('d/m/Y H:i', strtotime($booking['b_created_at'])) : '-';
                    $serviceDate = $booking['booking_date'] ? date('d/m/Y', strtotime($booking['booking_date'])) : '-';
                    $timeStart = $booking['time_start'] ? date('H:i', strtotime($booking['time_start'])) : '';
                    $timeEnd   = $booking['time_end'] ? date('H:i', strtotime($booking['time_end'])) : '';
                    $serviceRange = $serviceDate;
                    if ($timeStart || $timeEnd) {
                      $serviceRange .= ' ' . trim($timeStart . ($timeEnd ? ' - ' . $timeEnd : ''));
                    }
                    $statusCode = booking_status_code($booking['status']);
                    $badgeClass = booking_status_badge_class($statusCode);
                    $statusText = booking_status_label($statusCode);
                  ?>
                  <tr>
                    <td><?= (int)$booking['booking_id'] ?></td>
                    <td><?= safe($createdAt) ?></td>
                    <td><?= safe($serviceRange) ?></td>
                    <td>
                      <?php if (!empty($booking_services[$booking['booking_id']])): ?>
                        <?php foreach ($booking_services[$booking['booking_id']] as $service): ?>
                          <div><?= safe($service['service_name']) ?></div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td><?= safe($booking['staff_name'] ?? '-') ?></td>
                    <td class="text-end">€<?= number_format((float)$booking['total_price'], 2) ?></td>
                    <td class="text-end">€<?= number_format((float)$booking['total_discount'], 2) ?></td>
                    <td class="text-end text-success fw-bold">€<?= number_format((float)$booking['final_price'], 2) ?></td>
                    <td><span class="badge <?= safe($badgeClass) ?>"><?= safe($statusText) ?></span></td>
                  </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const periodSelect = document.getElementById('periodSelect');
  const sections = {
    day: document.getElementById('period-day'),
    month: document.getElementById('period-month'),
    year: document.getElementById('period-year')
  };
  function updatePeriodFields() {
    const value = periodSelect.value;
    Object.keys(sections).forEach(key => {
      if (sections[key]) {
        sections[key].classList.toggle('d-none', key !== value);
      }
    });
  }
  periodSelect.addEventListener('change', updatePeriodFields);
  updatePeriodFields();
});
</script>
</html>
