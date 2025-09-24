<?php
require_once('connect_db.php');
require_once('promotion_utils.php');

function esc($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function promotion_status_badge_class(string $status): string
{
    return match ($status) {
        'running'  => 'bg-success',
        'upcoming' => 'bg-warning text-dark',
        'ended'    => 'bg-secondary',
        default    => 'bg-light text-dark',
    };
}

$now = date('Y-m-d H:i:s');

// ---------------------------------------------------------------
// AJAX handler for chart + KPI data
// ---------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'stats') {
    header('Content-Type: application/json; charset=utf-8');

    $period       = $_GET['period'] ?? 'month'; // month|year|all
    $year         = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month        = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $startYear    = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $year - 3;
    $endYear      = isset($_GET['end_year']) ? (int)$_GET['end_year'] : $year;
    if ($startYear > $endYear) {
        [$startYear, $endYear] = [$endYear, $startYear];
    }

    $serviceId    = isset($_GET['service']) ? (int)$_GET['service'] : 0;
    $promotionId  = isset($_GET['promotion']) ? (int)$_GET['promotion'] : 0;
    $statusParam  = $_GET['booking_status'] ?? (string)BOOKING_STATUS_CONFIRMED;
    $statusCode   = $statusParam === 'all' ? null : booking_status_code($statusParam);

    // Build time buckets
    $labels = [];
    $bucketExpr = '';
    $axisLabel = '';
    $rangeWhere = [];
    $typesRange = '';
    $paramsRange = [];
    if ($period === 'month') {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($d = 1; $d <= $days; $d++) {
            $labels[] = str_pad((string)$d, 2, '0', STR_PAD_LEFT);
        }
        $bucketExpr = "DATE_FORMAT(b.booking_date,'%d')";
        $axisLabel = 'วัน';
        $rangeWhere[] = 'YEAR(b.booking_date) = ?';
        $typesRange .= 'i';
        $paramsRange[] = $year;
        $rangeWhere[] = 'MONTH(b.booking_date) = ?';
        $typesRange .= 'i';
        $paramsRange[] = $month;
        $rangeStart = sprintf('%04d-%02d-01', $year, $month);
        $rangeEnd   = sprintf('%04d-%02d-%02d', $year, $month, $days);
    } elseif ($period === 'year') {
        $labels = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $bucketExpr = "DATE_FORMAT(b.booking_date,'%m')";
        $axisLabel = 'เดือน';
        $rangeWhere[] = 'YEAR(b.booking_date) = ?';
        $typesRange .= 'i';
        $paramsRange[] = $year;
        $rangeStart = sprintf('%04d-01-01', $year);
        $rangeEnd   = sprintf('%04d-12-31', $year);
    } else { // all years
        if ($endYear < $startYear) {
            $endYear = $startYear;
        }
        for ($y = $startYear; $y <= $endYear; $y++) {
            $labels[] = (string)$y;
        }
        $bucketExpr = 'YEAR(b.booking_date)';
        $axisLabel = 'ปี';
        $rangeWhere[] = 'YEAR(b.booking_date) BETWEEN ? AND ?';
        $typesRange .= 'ii';
        $paramsRange[] = $startYear;
        $paramsRange[] = $endYear;
        $rangeStart = sprintf('%04d-01-01', $startYear);
        $rangeEnd   = sprintf('%04d-12-31', $endYear);
    }

    $whereParts = [];
    $whereParts[] = 'bs.discount_booking > 0';
    $whereParts[] = '(p.pm_start_date IS NULL OR CONCAT(b.booking_date, " ", b.time_start) >= p.pm_start_date)';
    $whereParts[] = '(p.pm_end_date   IS NULL OR CONCAT(b.booking_date, " ", b.time_start) <= p.pm_end_date)';
    if (!empty($rangeWhere)) {
        $whereParts = array_merge($whereParts, $rangeWhere);
    }
    $types = $typesRange;
    $params = $paramsRange;

    if ($statusCode !== null) {
        $whereParts[] = 'b.status = ?';
        $types .= 'i';
        $params[] = $statusCode;
    }
    if ($serviceId > 0) {
        $whereParts[] = 'pso.service_id = ?';
        $types .= 'i';
        $params[] = $serviceId;
    }
    if ($promotionId > 0) {
        $whereParts[] = 'pso.promotion_id = ?';
        $types .= 'i';
        $params[] = $promotionId;
    }

    $whereSql = implode(' AND ', $whereParts);

    // Chart series
    $chartSql = "
        SELECT {$bucketExpr} AS bucket,
               COUNT(DISTINCT b.booking_id)            AS booking_count,
               COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum,
               COALESCE(SUM(bs.net_price), 0)         AS net_sum,
               COALESCE(SUM(bs.price_booking), 0)     AS gross_sum
        FROM booking_seviceop bs
        JOIN booking b ON b.booking_id = bs.booking_id
        JOIN promotion_service_option pso ON pso.option_id = bs.option_id
        JOIN promotion p ON p.promotion_id = pso.promotion_id
        WHERE {$whereSql}
        GROUP BY bucket
        ORDER BY bucket
    ";
    $stmt = $conn->prepare($chartSql);
    if ($stmt === false) {
        echo json_encode(['error' => 'cannot_prepare']);
        exit;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $mapBookings = [];
    $mapDiscount = [];
    $mapNet = [];
    $mapGross = [];
    $totalDiscount = 0.0;
    $totalBookings = 0;
    $totalNet = 0.0;
    $totalGross = 0.0;
    while ($row = $res->fetch_assoc()) {
        $bucket = (string)$row['bucket'];
        $mapBookings[$bucket] = (int)$row['booking_count'];
        $mapDiscount[$bucket] = (float)$row['discount_sum'];
        $mapNet[$bucket] = (float)$row['net_sum'];
        $mapGross[$bucket] = (float)$row['gross_sum'];
        $totalDiscount += (float)$row['discount_sum'];
        $totalBookings += (int)$row['booking_count'];
        $totalNet += (float)$row['net_sum'];
        $totalGross += (float)$row['gross_sum'];
    }
    $stmt->close();

    $seriesDiscount = [];
    $seriesBookings = [];
    $seriesNet = [];
    foreach ($labels as $label) {
        $seriesDiscount[] = round($mapDiscount[$label] ?? 0, 2);
        $seriesBookings[] = (int)($mapBookings[$label] ?? 0);
        $seriesNet[] = round($mapNet[$label] ?? 0, 2);
    }

    // KPI cards (global counts)
    $runningCount = (int)$conn->query("SELECT COUNT(*) AS c FROM promotion WHERE pm_start_date <= '{$now}' AND pm_end_date >= '{$now}'")->fetch_assoc()['c'];
    $upcomingCount = (int)$conn->query("SELECT COUNT(*) AS c FROM promotion WHERE pm_start_date > '{$now}'")->fetch_assoc()['c'];
    $endedCount = (int)$conn->query("SELECT COUNT(*) AS c FROM promotion WHERE pm_end_date < '{$now}'")->fetch_assoc()['c'];

    // Top promotions in range (by discount value)
    $topSql = "
        SELECT p.promotion_id, p.pm_name,
               COUNT(DISTINCT b.booking_id)           AS booking_count,
               COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum
        FROM booking_seviceop bs
        JOIN booking b ON b.booking_id = bs.booking_id
        JOIN promotion_service_option pso ON pso.option_id = bs.option_id
        JOIN promotion p ON p.promotion_id = pso.promotion_id
        WHERE {$whereSql}
        GROUP BY p.promotion_id
        ORDER BY discount_sum DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($topSql);
    if ($stmt === false) {
        echo json_encode(['error' => 'cannot_prepare_top']);
        exit;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $topPromotions = [];
    while ($row = $res->fetch_assoc()) {
        $topPromotions[] = [
            'promotion_id' => (int)$row['promotion_id'],
            'promotion_name' => $row['pm_name'] ?? 'N/A',
            'bookings' => (int)$row['booking_count'],
            'discount' => (float)$row['discount_sum'],
        ];
    }
    $stmt->close();

    // Top services impacted
    $topServiceSql = "
        SELECT pso.service_id, s.service_name,
               COUNT(DISTINCT b.booking_id)           AS booking_count,
               COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum
        FROM booking_seviceop bs
        JOIN booking b ON b.booking_id = bs.booking_id
        JOIN promotion_service_option pso ON pso.option_id = bs.option_id
        JOIN promotion p ON p.promotion_id = pso.promotion_id
        LEFT JOIN service s ON s.service_id = pso.service_id
        WHERE {$whereSql}
        GROUP BY pso.service_id
        ORDER BY discount_sum DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($topServiceSql);
    if ($stmt === false) {
        echo json_encode(['error' => 'cannot_prepare_service']);
        exit;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $topServices = [];
    while ($row = $res->fetch_assoc()) {
        $topServices[] = [
            'service_id' => (int)$row['service_id'],
            'service_name' => $row['service_name'] ?? 'N/A',
            'bookings' => (int)$row['booking_count'],
            'discount' => (float)$row['discount_sum'],
        ];
    }
    $stmt->close();

    echo json_encode([
        'cards' => [
            'running' => $runningCount,
            'upcoming' => $upcomingCount,
            'ended' => $endedCount,
            'discount_total' => $totalDiscount,
            'bookings_total' => $totalBookings,
            'net_total' => $totalNet,
            'gross_total' => $totalGross,
        ],
        'chart' => [
            'labels' => $labels,
            'axis' => $axisLabel,
            'series' => [
                'discount' => $seriesDiscount,
                'bookings' => $seriesBookings,
                'net' => $seriesNet,
            ],
        ],
        'top_promotions' => $topPromotions,
        'top_services' => $topServices,
    ]);
    exit;
}
// ---------------------------------------------------------------
// TABLE view preparation
// ---------------------------------------------------------------
$search = trim($_GET['q'] ?? '');
$promoStatus = $_GET['promo_status'] ?? 'all'; // all|running|upcoming|ended
$serviceFilter = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$bookingStatusParam = $_GET['booking_status'] ?? (string)BOOKING_STATUS_CONFIRMED;
$bookingStatusCode = $bookingStatusParam === 'all' ? null : booking_status_code($bookingStatusParam);
$usageStart = $_GET['usage_start'] ?? '';
$usageEnd = $_GET['usage_end'] ?? '';
$sort = $_GET['sort'] ?? 'start';
$dir = strtoupper($_GET['dir'] ?? 'DESC');
$dir = $dir === 'ASC' ? 'ASC' : 'DESC';

$promoWhere = ['1=1'];
$promoTypes = '';
$promoParams = [];
if ($search !== '') {
    $promoWhere[] = 'p.pm_name LIKE ?';
    $promoTypes .= 's';
    $promoParams[] = "%{$search}%";
}
if ($promoStatus === 'running') {
    $promoWhere[] = 'p.pm_start_date <= ? AND p.pm_end_date >= ?';
    $promoTypes .= 'ss';
    $promoParams[] = $now;
    $promoParams[] = $now;
} elseif ($promoStatus === 'upcoming') {
    $promoWhere[] = 'p.pm_start_date > ?';
    $promoTypes .= 's';
    $promoParams[] = $now;
} elseif ($promoStatus === 'ended') {
    $promoWhere[] = 'p.pm_end_date < ?';
    $promoTypes .= 's';
    $promoParams[] = $now;
}
if ($serviceFilter > 0) {
    $promoWhere[] = 'EXISTS (SELECT 1 FROM promotion_service ps WHERE ps.promotion_id = p.promotion_id AND ps.service_id = ?)';
    $promoTypes .= 'i';
    $promoParams[] = $serviceFilter;
}
$promoWhereSql = implode(' AND ', $promoWhere);

$usageWhere = ['bs.discount_booking > 0'];
$usageTypes = '';
$usageParams = [];
$usageWhere[] = '(p2.pm_start_date IS NULL OR CONCAT(b.booking_date, " ", b.time_start) >= p2.pm_start_date)';
$usageWhere[] = '(p2.pm_end_date   IS NULL OR CONCAT(b.booking_date, " ", b.time_start) <= p2.pm_end_date)';
if ($bookingStatusCode !== null) {
    $usageWhere[] = 'b.status = ?';
    $usageTypes .= 'i';
    $usageParams[] = $bookingStatusCode;
}
if ($usageStart !== '') {
    $usageWhere[] = 'b.booking_date >= ?';
    $usageTypes .= 's';
    $usageParams[] = $usageStart;
}
if ($usageEnd !== '') {
    $usageWhere[] = 'b.booking_date <= ?';
    $usageTypes .= 's';
    $usageParams[] = $usageEnd;
}
if ($serviceFilter > 0) {
    $usageWhere[] = 'pso.service_id = ?';
    $usageTypes .= 'i';
    $usageParams[] = $serviceFilter;
}
$usageWhereSql = implode(' AND ', $usageWhere);

$sortMap = [
    'name' => 'p.pm_name ' . $dir,
    'start' => 'p.pm_start_date ' . $dir,
    'end' => 'p.pm_end_date ' . $dir,
    'services' => 'COALESCE(svc.service_count,0) ' . $dir,
    'options' => 'COALESCE(opt.option_count,0) ' . $dir,
    'bookings' => 'COALESCE(usage_stat.booking_count,0) ' . $dir,
    'discount' => 'COALESCE(usage_stat.discount_sum,0) ' . $dir,
    'net' => 'COALESCE(usage_stat.net_sum,0) ' . $dir,
    'status' => "CASE WHEN p.pm_start_date > '{$now}' THEN 1 WHEN p.pm_end_date < '{$now}' THEN 3 ELSE 2 END {$dir}",
];
$orderBy = $sortMap[$sort] ?? $sortMap['start'];

// Count total rows for pagination
$countSql = "SELECT COUNT(*) AS c FROM promotion p WHERE {$promoWhereSql}";
$countStmt = $conn->prepare($countSql);
if (!empty($promoParams)) {
    $countStmt->bind_param($promoTypes, ...$promoParams);
}
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$usageSubquery = "
    SELECT pso.promotion_id,
           COUNT(DISTINCT b.booking_id)           AS booking_count,
           COALESCE(SUM(bs.price_booking), 0)     AS gross_sum,
           COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum,
           COALESCE(SUM(bs.net_price), 0)         AS net_sum
    FROM booking_seviceop bs
    JOIN booking b ON b.booking_id = bs.booking_id
    JOIN promotion_service_option pso ON pso.option_id = bs.option_id
    JOIN promotion p2 ON p2.promotion_id = pso.promotion_id
    WHERE {$usageWhereSql}
    GROUP BY pso.promotion_id
";

$listSql = "
    SELECT
        p.promotion_id,
        p.pm_name,
        p.pm_start_date,
        p.pm_end_date,
        p.pm_created_at,
        p.description,
        p.percent,
        p.discount,
        COALESCE(svc.service_count, 0) AS service_count,
        COALESCE(opt.option_count, 0) AS option_count,
        COALESCE(opt.max_percent, 0) AS max_percent,
        COALESCE(opt.max_amount, 0) AS max_amount,
        COALESCE(usage_stat.booking_count, 0) AS booking_count,
        COALESCE(usage_stat.gross_sum, 0) AS gross_sum,
        COALESCE(usage_stat.discount_sum, 0) AS discount_sum,
        COALESCE(usage_stat.net_sum, 0) AS net_sum
    FROM promotion p
    LEFT JOIN (
        SELECT promotion_id, COUNT(DISTINCT service_id) AS service_count
        FROM promotion_service
        GROUP BY promotion_id
    ) svc ON svc.promotion_id = p.promotion_id
    LEFT JOIN (
        SELECT promotion_id,
               COUNT(*) AS option_count,
               MAX(discount_percent) AS max_percent,
               MAX(discount_amount) AS max_amount
        FROM promotion_service_option
        GROUP BY promotion_id
    ) opt ON opt.promotion_id = p.promotion_id
    LEFT JOIN (
        {$usageSubquery}
    ) usage_stat ON usage_stat.promotion_id = p.promotion_id
    WHERE {$promoWhereSql}
    ORDER BY {$orderBy}
    LIMIT ? OFFSET ?
";

$listStmt = $conn->prepare($listSql);
$paramsList = [];
$typesList = '';
if (!empty($usageParams)) {
    $typesList .= $usageTypes;
    $paramsList = array_merge($paramsList, $usageParams);
}
if (!empty($promoParams)) {
    $typesList .= $promoTypes;
    $paramsList = array_merge($paramsList, $promoParams);
}
$typesList .= 'ii';
$paramsList[] = $perPage;
$paramsList[] = $offset;

if (!empty($paramsList)) {
    $listStmt->bind_param($typesList, ...$paramsList);
}
$listStmt->execute();
$listRes = $listStmt->get_result();
$rows = [];
while ($row = $listRes->fetch_assoc()) {
    $rows[] = $row;
}
$listStmt->close();

// Totals across filtered dataset (within current page scope)
$tableTotals = [
    'booking_count' => 0,
    'gross_sum' => 0.0,
    'discount_sum' => 0.0,
    'net_sum' => 0.0,
];
foreach ($rows as $row) {
    $tableTotals['booking_count'] += (int)$row['booking_count'];
    $tableTotals['gross_sum'] += (float)$row['gross_sum'];
    $tableTotals['discount_sum'] += (float)$row['discount_sum'];
    $tableTotals['net_sum'] += (float)$row['net_sum'];
}

// Dropdown data
$serviceList = [];
$res = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");
while ($svc = $res->fetch_assoc()) {
    $serviceList[] = $svc;
}
$res->close();

$promotionList = [];
$res = $conn->query("SELECT promotion_id, pm_name FROM promotion ORDER BY pm_name");
while ($pr = $res->fetch_assoc()) {
    $promotionList[] = $pr;
}
$res->close();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Promotion Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    .small-label{font-size:.9rem;color:#6c757d}
    .kpi-value{font-size:26px;font-weight:700;margin:0}
    .card-icon{font-size:26px}
    .tab-toolbar{gap:1rem}
  </style>
</head>
<body>
<?php include('header.php'); ?>
<?php include('slidebar.php'); ?>
<main id="main" class="main">

  <div class="pagetitle"><h1>รายงานโปรโมชั่น (Promotion)</h1></div>

  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-table" type="button">ตาราง</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-chart" type="button" id="chart-tab">กราฟ</button></li>
  </ul>

  <div class="tab-content">
    <!-- TABLE TAB -->
    <div class="tab-pane fade show active" id="tab-table">
      <div class="card mt-3"><div class="card-body">
        <div class="d-flex flex-wrap tab-toolbar mb-3">
          <form class="row g-2 align-items-end" method="get">
            <div class="col-auto">
              <label class="form-label small-label">ค้นหา</label>
              <input type="text" class="form-control" name="q" value="<?=esc($search)?>" placeholder="ชื่อโปรโมชั่น">
            </div>
            <div class="col-auto">
              <label class="form-label small-label">สถานะโปรโมชั่น</label>
              <select class="form-select" name="promo_status">
                <option value="all" <?= $promoStatus==='all'?'selected':'' ?>>ทั้งหมด</option>
                <option value="running" <?= $promoStatus==='running'?'selected':'' ?>>กำลังใช้งาน</option>
                <option value="upcoming" <?= $promoStatus==='upcoming'?'selected':'' ?>>รอเริ่ม</option>
                <option value="ended" <?= $promoStatus==='ended'?'selected':'' ?>>สิ้นสุด</option>
              </select>
            </div>
            <div class="col-auto">
              <label class="form-label small-label">บริการ</label>
              <select class="form-select" name="service">
                <option value="0">ทั้งหมด</option>
                <?php foreach ($serviceList as $svc): ?>
                  <option value="<?=$svc['service_id']?>" <?= $serviceFilter==(int)$svc['service_id']?'selected':'' ?>><?=esc($svc['service_name'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-auto">
              <label class="form-label small-label">สถานะการจอง (ใช้นับสถิติ)</label>
              <select class="form-select" name="booking_status">
                <option value="all" <?= $bookingStatusParam==='all'?'selected':'' ?>>ทั้งหมด</option>
                <?php foreach (booking_status_options() as $code => $label): ?>
                  <option value="<?=$code?>" <?= (string)$code===(string)$bookingStatusParam?'selected':'' ?>><?=$label?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-auto">
              <label class="form-label small-label">ช่วงวันที่จอง</label>
              <div class="d-flex gap-2">
                <input type="date" class="form-control" name="usage_start" value="<?=esc($usageStart)?>">
                <input type="date" class="form-control" name="usage_end" value="<?=esc($usageEnd)?>">
              </div>
            </div>
            <div class="col-auto">
              <label class="form-label small-label">Sort</label>
              <div class="input-group">
                <select class="form-select" name="sort">
                  <option value="start" <?= $sort==='start'?'selected':'' ?>>เริ่มต้น</option>
                  <option value="end" <?= $sort==='end'?'selected':'' ?>>สิ้นสุด</option>
                  <option value="name" <?= $sort==='name'?'selected':'' ?>>ชื่อ</option>
                  <option value="services" <?= $sort==='services'?'selected':'' ?>>จำนวนบริการ</option>
                  <option value="options" <?= $sort==='options'?'selected':'' ?>>ตัวเลือก</option>
                  <option value="bookings" <?= $sort==='bookings'?'selected':'' ?>>จำนวนการใช้</option>
                  <option value="discount" <?= $sort==='discount'?'selected':'' ?>>ส่วนลดรวม</option>
                  <option value="net" <?= $sort==='net'?'selected':'' ?>>รายได้สุทธิ</option>
                  <option value="status" <?= $sort==='status'?'selected':'' ?>>สถานะ</option>
                </select>
                <select class="form-select" name="dir">
                  <option value="ASC" <?= $dir==='ASC'?'selected':'' ?>>ASC</option>
                  <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>DESC</option>
                </select>
              </div>
            </div>
            <div class="col-auto">
              <button class="btn btn-primary"><i class="bi bi-search"></i> ใช้ตัวกรอง</button>
            </div>
          </form>
          <div class="ms-auto d-flex flex-column flex-sm-row align-items-sm-center gap-2">
            <span class="badge bg-primary">โปรโมชั่น: <?=number_format($totalRows)?></span>
            <span class="badge bg-success">ส่วนลดรวมหน้า<?=number_format($tableTotals['discount_sum'],2)?></span>
            <span class="badge bg-secondary">ยอดสุทธิหน้า <?=number_format($tableTotals['net_sum'],2)?></span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>ชื่อโปรโมชั่น</th>
                <th>สถานะ</th>
                <th>ช่วงเวลา</th>
                <th class="text-center">บริการ</th>
                <th class="text-center">ตัวเลือก</th>
                <th class="text-end">ส่วนลดสูงสุด</th>
                <th class="text-end">จำนวนการใช้</th>
                <th class="text-end">ส่วนลดทั้งหมด</th>
                <th class="text-end">รายได้สุทธิ</th>
                <th>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="11" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach ($rows as $index => $row):
                $status = promotionStatus($row['pm_start_date'] ?? '', $row['pm_end_date'] ?? '');
                $badgeClass = promotion_status_badge_class($status);
                $statusLabel = promotionStatusLabel($status);
                $maxPercent = (float)$row['max_percent'];
                if ($maxPercent <= 0 && isset($row['percent'])) {
                    $maxPercent = (float)$row['percent'];
                }
                if ($maxPercent <= 0 && isset($row['discount'])) {
                    $maxPercent = (float)$row['discount'];
                }
              ?>
              <tr>
                <td><?= $offset + $index + 1 ?></td>
                <td>
                  <div class="fw-semibold"><?= esc($row['pm_name']) ?></div>
                  <?php if (!empty($row['description'])): ?>
                    <div class="text-muted small text-wrap" style="max-width:280px;"><?= esc(mb_strimwidth($row['description'],0,70,'…','UTF-8')) ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?=$badgeClass?>"><?= esc($statusLabel) ?></span></td>
                <td>
                  <div><?= esc(formatDateTimeDisplay($row['pm_start_date'] ?? '')) ?></div>
                  <div class="text-muted">ถึง <?= esc(formatDateTimeDisplay($row['pm_end_date'] ?? '')) ?></div>
                </td>
                <td class="text-center"><?= number_format((int)$row['service_count']) ?></td>
                <td class="text-center"><?= number_format((int)$row['option_count']) ?></td>
                <td class="text-end"><?= number_format($maxPercent, 2) ?>%</td>
                <td class="text-end"><span class="badge bg-info text-dark"><?= number_format((int)$row['booking_count']) ?></span></td>
                <td class="text-end text-danger">-<?= number_format((float)$row['discount_sum'], 2) ?></td>
                <td class="text-end text-success fw-semibold"><?= number_format((float)$row['net_sum'], 2) ?></td>
                <td>
                  <div class="btn-group">
                    <a href="promotion_detail.php?id=<?=$row['promotion_id']?>" class="btn btn-sm btn-outline-primary" title="รายละเอียด"><i class="bi bi-eye"></i></a>
                    <a href="promotion_update_form.php?id=<?=$row['promotion_id']?>" class="btn btn-sm btn-outline-secondary" title="แก้ไข"><i class="bi bi-pencil"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot>
              <tr class="table-light">
                <th colspan="7" class="text-end">รวม (เฉพาะรายการในหน้านี้)</th>
                <th class="text-end"><?= number_format($tableTotals['booking_count']) ?></th>
                <th class="text-end text-danger">-<?= number_format($tableTotals['discount_sum'],2) ?></th>
                <th class="text-end text-success"><?= number_format($tableTotals['net_sum'],2) ?></th>
                <th></th>
              </tr>
            </tfoot>
            <?php endif; ?>
          </table>
        </div>

        <!-- Pagination -->
        <?php $totalPages = (int)ceil($totalRows / $perPage); if ($totalPages > 1): ?>
        <nav>
          <ul class="pagination justify-content-end">
            <?php for ($i=1;$i<=$totalPages;$i++):
              $query = $_GET; $query['page']=$i; ?>
              <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query($query) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>

      </div></div>
    </div>

    <!-- CHART TAB -->
    <div class="tab-pane fade" id="tab-chart">
      <div class="card mt-3"><div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-auto">
            <label class="form-label small-label">ช่วงเวลา</label>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="chart_period" id="chart_all" value="all">
              <label class="btn btn-outline-primary" for="chart_all">All</label>
              <input type="radio" class="btn-check" name="chart_period" id="chart_month" value="month" checked>
              <label class="btn btn-outline-primary" for="chart_month">Month</label>
              <input type="radio" class="btn-check" name="chart_period" id="chart_year" value="year">
              <label class="btn btn-outline-primary" for="chart_year">Year</label>
            </div>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">บริการ</label>
            <select id="chart_service" class="form-select">
              <option value="0">ทั้งหมด</option>
              <?php foreach ($serviceList as $svc): ?>
                <option value="<?=$svc['service_id']?>"><?=esc($svc['service_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">โปรโมชั่น</label>
            <select id="chart_promotion" class="form-select">
              <option value="0">ทั้งหมด</option>
              <?php foreach ($promotionList as $pr): ?>
                <option value="<?=$pr['promotion_id']?>"><?=esc($pr['pm_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">สถานะการจอง</label>
            <select id="chart_status" class="form-select">
              <option value="all">ทั้งหมด</option>
              <?php foreach (booking_status_options() as $code => $label): ?>
                <option value="<?=$code?>" <?= (string)$code===(string)$bookingStatusParam?'selected':'' ?>><?=$label?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-auto" id="ctl-month">
            <label class="form-label small-label">เดือน/ปี</label>
            <div class="d-flex gap-2">
              <select id="chart_month_sel" class="form-select">
                <?php for ($m=1;$m<=12;$m++): ?>
                  <option value="<?=$m?>" <?= $m==(int)date('n')?'selected':'' ?>><?=$m?></option>
                <?php endfor; ?>
              </select>
              <select id="chart_year_sel" class="form-select">
                <?php for ($y=date('Y')-3;$y<=date('Y')+1;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto d-none" id="ctl-year">
            <label class="form-label small-label">ปี</label>
            <select id="chart_year_only" class="form-select">
              <?php for ($y=date('Y')-5;$y<=date('Y')+1;$y++): ?>
                <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-auto d-none" id="ctl-all">
            <label class="form-label small-label">ช่วงปี</label>
            <div class="d-flex gap-2">
              <select id="chart_start_year" class="form-select">
                <?php for ($y=date('Y')-5;$y<=date('Y')+1;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')-1?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
              <select id="chart_end_year" class="form-select">
                <?php for ($y=date('Y')-5;$y<=date('Y')+1;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button id="applyChart" class="btn btn-primary"><i class="bi bi-funnel"></i> ใช้ตัวกรอง</button>
          </div>
        </div>

        <div class="row mt-3 g-3">
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-megaphone-fill card-icon text-primary"></i>
            <div><div class="kpi-value" id="k_running">0</div><div class="text-muted">โปรโมชั่นที่กำลังใช้งาน</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-clock-history card-icon text-warning"></i>
            <div><div class="kpi-value" id="k_upcoming">0</div><div class="text-muted">โปรโมชั่นที่รอเริ่ม</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-patch-check card-icon text-success"></i>
            <div><div class="kpi-value" id="k_bookings">0</div><div class="text-muted">การจองที่ใช้โปรโมชั่น</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-currency-baht card-icon text-danger"></i>
            <div><div class="kpi-value" id="k_discount">0</div><div class="text-muted">ส่วนลดรวมช่วงที่เลือก</div></div>
          </div></div></div>
        </div>
        <div class="text-muted">* การ์ดซ้าย 2 ใบเป็นข้อมูลรวมทั้งหมด ส่วนด้านขวาอิงช่วงเวลาที่กรอง</div>

        <div class="mt-3" style="min-height:360px">
          <canvas id="promoChart" height="120"></canvas>
        </div>
        <div class="text-muted mt-2">แท่ง: ส่วนลดรวม &nbsp;—&nbsp; เส้น: จำนวนการใช้โปรโมชั่น</div>

        <div class="row mt-4 g-3">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">Top โปรโมชั่น (ตามมูลค่าส่วนลด)</div>
              <div class="card-body">
                <ul class="list-group" id="topPromotions"></ul>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">Top บริการที่ได้รับส่วนลด</div>
              <div class="card-body">
                <ul class="list-group" id="topServices"></ul>
              </div>
            </div>
          </div>
        </div>

      </div></div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateChartControls(){
  const period=document.querySelector('input[name=chart_period]:checked')?.value||'month';
  document.getElementById('ctl-month').classList.toggle('d-none',period!=='month');
  document.getElementById('ctl-year').classList.toggle('d-none',period!=='year');
  document.getElementById('ctl-all').classList.toggle('d-none',period!=='all');
}
['chart_all','chart_month','chart_year'].forEach(id=>{
  document.getElementById(id).addEventListener('change',updateChartControls);
});
updateChartControls();

let promoChart;
async function loadPromoStats(){
  const period=document.querySelector('input[name=chart_period]:checked')?.value||'month';
  const url=new URL(location.href);
  url.search='';
  url.searchParams.set('action','stats');
  url.searchParams.set('period',period);
  url.searchParams.set('service',document.getElementById('chart_service').value);
  url.searchParams.set('promotion',document.getElementById('chart_promotion').value);
  url.searchParams.set('booking_status',document.getElementById('chart_status').value);
  if(period==='month'){
    url.searchParams.set('year',document.getElementById('chart_year_sel').value);
    url.searchParams.set('month',document.getElementById('chart_month_sel').value);
  }else if(period==='year'){
    url.searchParams.set('year',document.getElementById('chart_year_only').value);
  }else{
    url.searchParams.set('start_year',document.getElementById('chart_start_year').value);
    url.searchParams.set('end_year',document.getElementById('chart_end_year').value);
  }

  const res=await fetch(url.toString(),{cache:'no-store'});
  if(!res.ok){
    console.error('Failed to load stats');
    return;
  }
  const data=await res.json();

  const nf=v=>Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('k_running').textContent=Number(data.cards?.running||0).toLocaleString();
  document.getElementById('k_upcoming').textContent=Number(data.cards?.upcoming||0).toLocaleString();
  document.getElementById('k_bookings').textContent=Number(data.cards?.bookings_total||0).toLocaleString();
  document.getElementById('k_discount').textContent=nf(data.cards?.discount_total||0);

  const labels=data.chart?.labels||[];
  const discount=data.chart?.series?.discount||[];
  const bookings=data.chart?.series?.bookings||[];

  if(promoChart) promoChart.destroy();
  const ctx=document.getElementById('promoChart').getContext('2d');
  promoChart=new Chart(ctx,{
    type:'bar',
    data:{
      labels,
      datasets:[
        {label:'ส่วนลดรวม', data:discount, backgroundColor:'rgba(220,53,69,0.5)', borderColor:'rgba(220,53,69,1)', borderWidth:1},
        {label:'ยอดใช้โปรโมชั่น', data:bookings, type:'line', yAxisID:'y1', tension:0.3, borderColor:'#0d6efd', backgroundColor:'rgba(13,110,253,0.2)', borderWidth:2}
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        x:{title:{display:true,text:data.chart?.axis||''}},
        y:{title:{display:true,text:'ส่วนลด (฿)'},beginAtZero:true},
        y1:{title:{display:true,text:'จำนวนการใช้'},beginAtZero:true,position:'right',grid:{drawOnChartArea:false}}
      },
      plugins:{legend:{display:true},tooltip:{mode:'index',intersect:false}}
    }
  });

  const topPromoList=document.getElementById('topPromotions');
  topPromoList.innerHTML='';
  (data.top_promotions||[]).forEach(item=>{
    const li=document.createElement('li');
    li.className='list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML=`<span>${item.promotion_name || 'N/A'}</span><span class="badge bg-danger">-฿${nf(item.discount || 0)}</span>`;
    topPromoList.appendChild(li);
  });
  if(topPromoList.childElementCount===0){
    const li=document.createElement('li');
    li.className='list-group-item text-muted';
    li.textContent='ไม่มีข้อมูลในช่วงที่เลือก';
    topPromoList.appendChild(li);
  }

  const topSvcList=document.getElementById('topServices');
  topSvcList.innerHTML='';
  (data.top_services||[]).forEach(item=>{
    const li=document.createElement('li');
    li.className='list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML=`<span>${item.service_name || 'N/A'}</span><span class="badge bg-info text-dark">-฿${nf(item.discount || 0)}</span>`;
    topSvcList.appendChild(li);
  });
  if(topSvcList.childElementCount===0){
    const li=document.createElement('li');
    li.className='list-group-item text-muted';
    li.textContent='ไม่มีข้อมูลในช่วงที่เลือก';
    topSvcList.appendChild(li);
  }
}

document.getElementById('applyChart').addEventListener('click',function(ev){
  ev.preventDefault();
  loadPromoStats();
});

document.getElementById('chart-tab')?.addEventListener('shown.bs.tab',()=>loadPromoStats());
</script>
</body>
</html>
