<?php
// report_staff.php — Staff Report
// Focused only on staff-related analytics.
// - TAB: TABLE (summary by staff in a chosen date range)
// - TAB: CHART (time-series + top staff leaderboards, with filters)
// - KPI cards are GLOBAL (not affected by chart filters), for overall picture.
//
// Schema used (from your project):
//   staff(staff_id, staff_name, ...)
//   booking(booking_id, customer_id, staff_id, booking_date, time_start, time_end, final_price, total_price, total_discount, status)
//   booking_seviceop(booking_detail_id, booking_id, option_id, net_price, ...)
//   service_option(option_id, service_id)
//   service(service_id, service_name)
//   customer(customer_id, customer_name, gmail)
require_once("connect_db.php");
$pendingStatus   = BOOKING_STATUS_PENDING;
$confirmedStatus = BOOKING_STATUS_CONFIRMED;
$completedStatus = BOOKING_STATUS_COMPLATE;
$cancelledStatus = BOOKING_STATUS_CANCELLED;
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// ---------------- Year meta for chart pickers ----------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ==================== Helpers & AJAX ====================
function lastDayOfMonth(int $year, int $month): int {
  return (int)date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
}

function resolveTableDateRange(array $input): array {
  $type = $input['date_filter_type'] ?? 'range';
  $type = in_array($type, ['range', 'month'], true) ? $type : 'range';
  $startDate = trim($input['start_date'] ?? '');
  $endDate   = trim($input['end_date'] ?? '');
  $monthVal  = trim($input['month_value'] ?? '');

  if ($type === 'month') {
    if (preg_match('/^(\d{4})-(\d{2})$/', $monthVal, $m)) {
      $y = (int)$m[1];
      $mo = (int)$m[2];
      $startDate = sprintf('%04d-%02d-01', $y, $mo);
      $endDate = sprintf('%04d-%02d-%02d', $y, $mo, lastDayOfMonth($y, $mo));
    } else {
      $startDate = '';
      $endDate = '';
    }
  } else {
    if ($startDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
      $startDate = '';
    }
    if ($endDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
      $endDate = '';
    }
  }

  if ($startDate !== '' && $endDate !== '' && $startDate > $endDate) {
    $tmp = $startDate;
    $startDate = $endDate;
    $endDate = $tmp;
  }

  return [
    'type' => $type,
    'start' => $startDate,
    'end' => $endDate,
    'month_value' => $type === 'month' ? $monthVal : ''
  ];
}

$dateBounds = $conn->query("SELECT MIN(booking_date) AS min_date, MAX(booking_date) AS max_date FROM booking")->fetch_assoc();
$globalMinDate = $dateBounds['min_date'] ?? date('Y-01-01');
$globalMaxDate = $dateBounds['max_date'] ?? date('Y-m-d');

function resolveChartRange(string $period, array $input, string $globalMin, string $globalMax): array {
  $period = in_array($period, ['all', 'year', 'month', 'day'], true) ? $period : 'all';
  $rangeStart = $globalMin;
  $rangeEnd   = $globalMax;
  $granularity = 'year';

  if ($period === 'year') {
    $startYear = isset($input['start_year']) ? (int)$input['start_year'] : (int)date('Y');
    $endYear   = isset($input['end_year'])   ? (int)$input['end_year']   : $startYear;
    if ($startYear > $endYear) { $tmp=$startYear; $startYear=$endYear; $endYear=$tmp; }
    $rangeStart = sprintf('%04d-01-01', $startYear);
    $rangeEnd   = sprintf('%04d-12-31', $endYear);
    $granularity = 'month';
  } elseif ($period === 'month') {
    $startMonth = $input['start_month'] ?? date('Y-m');
    $endMonth   = $input['end_month']   ?? $startMonth;
    if (!preg_match('/^(\d{4})-(\d{2})$/', $startMonth)) {
      $startMonth = date('Y-m');
    }
    if (!preg_match('/^(\d{4})-(\d{2})$/', $endMonth)) {
      $endMonth = $startMonth;
    }
    $startTime = strtotime($startMonth . '-01');
    $endTime   = strtotime($endMonth . '-01');
    if ($startTime !== false && $endTime !== false) {
      if ($startTime > $endTime) { $tmp=$startTime; $startTime=$endTime; $endTime=$tmp; }
      $sY = (int)date('Y', $startTime); $sM = (int)date('n', $startTime);
      $eY = (int)date('Y', $endTime);   $eM = (int)date('n', $endTime);
      $rangeStart = sprintf('%04d-%02d-01', $sY, $sM);
      $rangeEnd   = sprintf('%04d-%02d-%02d', $eY, $eM, lastDayOfMonth($eY, $eM));
    }
    $granularity = 'day';
  } elseif ($period === 'day') {
    $startDate = $input['start_date'] ?? date('Y-m-d');
    $endDate   = $input['end_date']   ?? $startDate;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
      $startDate = $globalMin;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
      $endDate = $startDate;
    }
    if ($startDate > $endDate) { $tmp=$startDate; $startDate=$endDate; $endDate=$tmp; }
    $rangeStart = $startDate;
    $rangeEnd   = $endDate;
    $granularity = 'day';
  }

  if ($rangeStart === null || $rangeStart === '') { $rangeStart = $globalMin; }
  if ($rangeEnd === null || $rangeEnd === '')     { $rangeEnd   = $globalMax; }

  return [
    'period' => $period,
    'start' => $rangeStart,
    'end'   => $rangeEnd,
    'granularity' => $granularity
  ];
}

if (isset($_GET['action'])) {
  $action = $_GET['action'];
  if ($action === 'chart_overview') {
    header('Content-Type: application/json; charset=utf-8');
    $period = $_GET['period'] ?? 'all';
    $range = resolveChartRange($period, $_GET, $globalMinDate, $globalMaxDate);

    $joinConds = [];
    if ($range['start']) { $joinConds[] = "b.booking_date >= '" . $conn->real_escape_string($range['start']) . "'"; }
    if ($range['end'])   { $joinConds[] = "b.booking_date <= '" . $conn->real_escape_string($range['end']) . "'"; }
    $joinFilter = $joinConds ? implode(' AND ', $joinConds) : '1=1';

    $sql = "
      SELECT
        s.staff_id,
        s.staff_name,
        COUNT(b.booking_id) AS total,
        SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN b.status={$completedStatus} THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS cancelled
      FROM staff s
      LEFT JOIN booking b ON b.staff_id=s.staff_id AND $joinFilter
      WHERE LOWER(COALESCE(s.st_status,'')) IN ('active','')
      GROUP BY s.staff_id
      ORDER BY s.staff_name
    ";
    $rs = $conn->query($sql);
    $staffs = [];
    while ($row = $rs->fetch_assoc()) {
      $staffs[] = [
        'id' => (int)$row['staff_id'],
        'name' => $row['staff_name'] ?: 'N/A',
        'total' => (int)($row['total'] ?? 0),
        'confirmed' => (int)($row['confirmed'] ?? 0),
        'completed' => (int)($row['completed'] ?? 0),
        'pending' => (int)($row['pending'] ?? 0),
        'cancelled' => (int)($row['cancelled'] ?? 0)
      ];
    }

    $top = $staffs;
    usort($top, function ($a, $b) {
      if ($a['total'] === $b['total']) {
        return $b['confirmed'] <=> $a['confirmed'];
      }
      return $b['total'] <=> $a['total'];
    });
    $top = array_slice($top, 0, 10);

    echo json_encode([
      'range' => $range,
      'staffs' => $staffs,
      'top' => $top
    ]);
    exit;
  }

  if ($action === 'chart_staff_detail') {
    header('Content-Type: application/json; charset=utf-8');
    $staffId = isset($_GET['staff_id']) ? (int)$_GET['staff_id'] : 0;
    if ($staffId <= 0) {
      echo json_encode(['labels' => [], 'series' => []]);
      exit;
    }

    $period = $_GET['period'] ?? 'all';
    $range = resolveChartRange($period, $_GET, $globalMinDate, $globalMaxDate);

    $labels = [];
    $groupExpr = '';
    if ($range['granularity'] === 'day') {
      $groupExpr = "DATE_FORMAT(b.booking_date,'%Y-%m-%d')";
      $start = new DateTime($range['start']);
      $end = new DateTime($range['end']);
      $end->modify('+1 day');
      $periodObj = new DatePeriod($start, new DateInterval('P1D'), $end);
      foreach ($periodObj as $dt) { $labels[] = $dt->format('Y-m-d'); }
    } elseif ($range['granularity'] === 'month') {
      $groupExpr = "DATE_FORMAT(b.booking_date,'%Y-%m')";
      $start = new DateTime(substr($range['start'], 0, 7) . '-01');
      $end = new DateTime(substr($range['end'], 0, 7) . '-01');
      $end->modify('+1 month');
      $periodObj = new DatePeriod($start, new DateInterval('P1M'), $end);
      foreach ($periodObj as $dt) { $labels[] = $dt->format('Y-m'); }
    } else {
      $groupExpr = "DATE_FORMAT(b.booking_date,'%Y')";
      $startYear = (int)substr($range['start'], 0, 4);
      $endYear   = (int)substr($range['end'], 0, 4);
      if ($startYear > $endYear) { $tmp=$startYear; $startYear=$endYear; $endYear=$tmp; }
      for ($y=$startYear; $y<=$endYear; $y++) { $labels[] = (string)$y; }
    }

    $conditions = ["b.staff_id=$staffId"];
    if ($range['start']) { $conditions[] = "b.booking_date >= '" . $conn->real_escape_string($range['start']) . "'"; }
    if ($range['end'])   { $conditions[] = "b.booking_date <= '" . $conn->real_escape_string($range['end']) . "'"; }
    $where = implode(' AND ', $conditions);

    $sql = "
      SELECT $groupExpr AS bucket,
             COUNT(*) AS total,
             SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS confirmed,
             SUM(CASE WHEN b.status={$completedStatus} THEN 1 ELSE 0 END) AS completed,
             SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS pending,
             SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS cancelled
      FROM booking b
      WHERE $where
      GROUP BY bucket
      ORDER BY bucket
    ";
    $rs = $conn->query($sql);
    $map = [];
    while ($row = $rs->fetch_assoc()) {
      $bucket = (string)$row['bucket'];
      $map[$bucket] = [
        'total' => (int)($row['total'] ?? 0),
        'confirmed' => (int)($row['confirmed'] ?? 0),
        'completed' => (int)($row['completed'] ?? 0),
        'pending' => (int)($row['pending'] ?? 0),
        'cancelled' => (int)($row['cancelled'] ?? 0)
      ];
    }

    $series = [
      'total' => [],
      'confirmed' => [],
      'completed' => [],
      'pending' => [],
      'cancelled' => []
    ];
    foreach ($labels as $label) {
      $series['total'][]     = $map[$label]['total']     ?? 0;
      $series['confirmed'][] = $map[$label]['confirmed'] ?? 0;
      $series['completed'][] = $map[$label]['completed'] ?? 0;
      $series['pending'][]   = $map[$label]['pending']   ?? 0;
      $series['cancelled'][] = $map[$label]['cancelled'] ?? 0;
    }

    echo json_encode([
      'labels' => $labels,
      'series' => $series,
      'range'  => $range
    ]);
    exit;
  }

  if ($action === 'staff-bookings') {
    header('Content-Type: application/json; charset=utf-8');
    $staffId = isset($_GET['staff_id']) ? (int)$_GET['staff_id'] : 0;
    $statusKey = $_GET['status'] ?? '';
    $statusMap = [
      'total' => null,
      'confirmed' => $confirmedStatus,
      'completed' => $completedStatus,
      'pending' => $pendingStatus,
      'cancelled' => $cancelledStatus
    ];
    if ($staffId <= 0 || !array_key_exists($statusKey, $statusMap)) {
      echo json_encode(['bookings' => []]);
      exit;
    }

    $rangeTable = resolveTableDateRange($_GET);
    $startDate = $rangeTable['start'];
    $endDate   = $rangeTable['end'];
    $serviceId = isset($_GET['service']) ? (int)$_GET['service'] : 0;

    $where = ["b.staff_id=$staffId"];
    if ($statusMap[$statusKey] !== null) {
      $where[] = "b.status=" . (int)$statusMap[$statusKey];
    }
    if ($startDate !== '') {
      $where[] = "b.booking_date >= '" . $conn->real_escape_string($startDate) . "'";
    }
    if ($endDate !== '') {
      $where[] = "b.booking_date <= '" . $conn->real_escape_string($endDate) . "'";
    }
    if ($serviceId > 0) {
      $where[] = "EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=$serviceId)";
    }
    $whereSql = implode(' AND ', $where);

    $sql = "
      SELECT
        b.booking_id,
        b.booking_date,
        b.time_start,
        b.time_end,
        b.final_price,
        b.status,
        c.customer_name,
        GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', ') AS services
      FROM booking b
      LEFT JOIN customer c ON c.customer_id=b.customer_id
      LEFT JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      LEFT JOIN service_option so ON so.option_id=bs.option_id
      LEFT JOIN service sv ON sv.service_id=so.service_id
      WHERE $whereSql
      GROUP BY b.booking_id
      ORDER BY b.booking_date DESC, b.time_start DESC
    ";
    $rs = $conn->query($sql);
    $bookings = [];
    while ($row = $rs->fetch_assoc()) {
      $bookings[] = [
        'id' => (int)$row['booking_id'],
        'date' => $row['booking_date'],
        'time_start' => $row['time_start'],
        'time_end' => $row['time_end'],
        'price' => (float)($row['final_price'] ?? 0),
        'status' => (int)$row['status'],
        'customer' => $row['customer_name'] ?? '-',
        'services' => $row['services'] ?? ''
      ];
    }

    echo json_encode(['bookings' => $bookings]);
    exit;
  }
}

// ==================== TABLE: summary by staff ====================
$search    = trim($_GET['q'] ?? '');
$serviceF  = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$rangeTable = resolveTableDateRange($_GET);
$filterType = $rangeTable['type'];
$startDate  = $rangeTable['start'];
$endDate    = $rangeTable['end'];
$monthValue = $rangeTable['month_value'];
$sort       = $_GET['sort'] ?? 'revenue';
$dirDefault = $sort === 'name' ? 'ASC' : 'DESC';
$dirParam   = strtoupper($_GET['dir'] ?? $dirDefault);
$dir        = in_array($dirParam, ['ASC','DESC'], true) ? $dirParam : $dirDefault;

$sortMap=[
  'name'       => "s.staff_name $dir",
  'revenue'    => "net_rev $dir",
  'popularity' => "tx_total $dir",
  'hours'      => "hrs $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['revenue'];

$activeStaffTotalRow = $conn->query("SELECT COUNT(*) AS c FROM staff WHERE LOWER(COALESCE(st_status,'')) IN ('active','')")->fetch_assoc();
$activeStaffTotal = (int)($activeStaffTotalRow['c'] ?? 0);

// WHERE for bookings range
$wb = [];
if ($startDate!=='') $wb[]="b.booking_date>='" . $conn->real_escape_string($startDate) . "'";
if ($endDate!=='')   $wb[]="b.booking_date<='" . $conn->real_escape_string($endDate) . "'";
if ($serviceF>0)     $wb[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=$serviceF)";
$wbSql = $wb ? implode(' AND ', $wb) : '1=1';

// Aggregation per staff
$sql="
  SELECT
    s.staff_id, s.staff_name,
    COUNT(b.booking_id) AS tx_total,
    SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS tx_conf,
    SUM(CASE WHEN b.status={$completedStatus} THEN 1 ELSE 0 END) AS tx_comp,
    SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS tx_pend,
    SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS tx_canc,
    COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) AS net_rev,
    CASE WHEN SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END)=0
         THEN 0
         ELSE COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) /
              NULLIF(SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END),0)
    END AS aov,
    COUNT(DISTINCT CASE WHEN b.status={$confirmedStatus} THEN b.customer_id END) AS custs,
    COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(b.time_end, b.time_start)))/3600,0) AS hrs,
    AVG(TIME_TO_SEC(TIMEDIFF(b.time_end, b.time_start))/60) AS avgdur,
    MIN(b.booking_date) AS first_bk,
    MAX(b.booking_date) AS last_bk
  FROM staff s
  LEFT JOIN booking b ON b.staff_id=s.staff_id AND $wbSql
  WHERE LOWER(COALESCE(s.st_status,'')) IN ('active','')
  GROUP BY s.staff_id
  ORDER BY $orderBy
";
$rs=$conn->query($sql);
$rows=[];
while($r=$rs->fetch_assoc()){
  $rows[]=$r;
}

if ($search!=='') {
  $rows = array_values(array_filter($rows, function($r) use ($search){
    return mb_stripos($r['staff_name'] ?? '', $search) !== false;
  }));
}

$filteredCount = count($rows);
$totalRows = $filteredCount;
$summary = [
  'tx_total' => 0,
  'tx_conf'  => 0,
  'tx_comp'  => 0,
  'tx_pend'  => 0,
  'tx_canc'  => 0,
  'net_rev'  => 0.0,
  'custs'    => 0,
  'hrs'      => 0.0,
  'aov'      => 0.0,
  'avgdur'   => 0.0,
];
foreach ($rows as $row) {
  $summary['tx_total'] += (int)($row['tx_total'] ?? 0);
  $summary['tx_conf']  += (int)($row['tx_conf'] ?? 0);
  $summary['tx_comp']  += (int)($row['tx_comp'] ?? 0);
  $summary['tx_pend']  += (int)($row['tx_pend'] ?? 0);
  $summary['tx_canc']  += (int)($row['tx_canc'] ?? 0);
  $summary['net_rev']  += (float)($row['net_rev'] ?? 0);
  $summary['custs']    += (int)($row['custs'] ?? 0);
  $summary['hrs']      += (float)($row['hrs'] ?? 0);
}
$summary['aov']    = $summary['tx_conf'] > 0 ? $summary['net_rev'] / $summary['tx_conf'] : 0.0;
$summary['avgdur'] = $summary['tx_total'] > 0 ? ($summary['hrs'] * 60) / $summary['tx_total'] : 0.0;

$per = 20;
$pages = max(1, (int)ceil($totalRows / $per));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $per;
$rows = array_slice($rows, $offset, $per);

// Dropdown options
$serviceOps = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");

$baseQuery = $_GET;
unset($baseQuery['page'], $baseQuery['tab'], $baseQuery['action']);
$baseQuery['tab'] = 'table';
$pageUrl = function(int $target) use ($baseQuery): string {
  $query = $baseQuery;
  $query['page'] = $target;
  return '?' . http_build_query($query);
};
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Staff Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .small-label{font-size:.9rem;color:#6c757d}
    .kpi-value{font-size:28px;font-weight:700;margin:0}
    .card-icon{font-size:28px}
    .table-sm td, .table-sm th { padding: .45rem .5rem; }
    .chart-wrapper{position:relative;height:360px}
    #staffMainChart{width:100% !important;height:100% !important}
    @media (max-width: 992px){.chart-wrapper{height:320px}}
  </style>
</head>
<body>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>
<main id="main" class="main">

  <div class="pagetitle"><h1>รายงานสตาฟ (Staff)</h1></div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-table" type="button">ตาราง</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-chart" type="button" id="chart-tab">กราฟ</button></li>
  </ul>

  <div class="tab-content">
    <!-- TAB: TABLE -->
    <div class="tab-pane fade show active" id="t-table">
      <div class="card mt-3"><div class="card-body">

        <form class="row g-2 align-items-end" method="get" id="staffFilterForm">
          <input type="hidden" name="tab" value="table">
          <div class="col-md-3 col-lg-2">
            <label class="form-label small-label" for="staffSearch">ค้นหาสตาฟ</label>
            <input type="text" class="form-control" id="staffSearch" name="q" value="<?=esc($search)?>" placeholder="ชื่อสตาฟ">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label" for="serviceFilter">บริการ</label>
            <select class="form-select" id="serviceFilter" name="service">
              <option value="0">ทั้งหมด</option>
              <?php while($sv=$serviceOps->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>" <?= $serviceF==$sv['service_id']?'selected':'' ?>><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label" for="date_filter_type">ช่วงเวลา</label>
            <select class="form-select" id="date_filter_type" name="date_filter_type">
              <option value="range" <?= $filterType==='range'?'selected':'' ?>>ช่วงวันที่</option>
              <option value="month" <?= $filterType==='month'?'selected':'' ?>>เดือน</option>
            </select>
          </div>
          <div class="col-md-auto <?= $filterType==='range'?'':'d-none' ?>" id="date_range_group">
            <label class="form-label small-label">วันที่</label>
            <div class="d-flex gap-2">
              <input type="date" class="form-control" name="start_date" value="<?=esc($filterType==='range'?$startDate:'')?>">
              <input type="date" class="form-control" name="end_date"   value="<?=esc($filterType==='range'?$endDate:'')?>">
            </div>
          </div>
          <div class="col-md-auto <?= $filterType==='month'?'':'d-none' ?>" id="month_group">
            <label class="form-label small-label" for="month_value">เดือน</label>
            <input type="month" class="form-control" id="month_value" name="month_value" value="<?=esc($filterType==='month'?$monthValue:'')?>">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label" for="sort_field">เรียงตาม</label>
            <div class="input-group">
              <select class="form-select" id="sort_field" name="sort">
                <option value="name" <?= $sort==='name'?'selected':'' ?>>ชื่อ</option>
                <option value="popularity" <?= $sort==='popularity'?'selected':'' ?>>ความนิยม</option>
                <option value="hours" <?= $sort==='hours'?'selected':'' ?>>จำนวนชั่วโมง</option>
                <option value="revenue" <?= $sort==='revenue'?'selected':'' ?>>รายได้</option>
              </select>
              <select class="form-select" id="sort_dir" name="dir">
                <option value="ASC"  <?= $dir==='ASC'?'selected':'' ?>>A → Z</option>
                <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>Z → A</option>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> ใช้ตัวกรอง</button>
          </div>
        </form>

        <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
          <span class="badge bg-secondary">พนักงาน Active ทั้งหมด: <?=number_format($activeStaffTotal)?> คน</span>
          <span class="badge bg-primary">ผลลัพธ์จากตัวกรอง: <?=number_format($filteredCount)?> คน</span>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle table-sm">
            <thead class="table-light">
              <tr>
                <th>Staff</th>
                <th class="text-end">Bookings</th>
                <th class="text-end">Confirmed</th>
                <th class="text-end">Complate</th>
                <th class="text-end">Pending</th>
                <th class="text-end">Cancelled</th>
                <th class="text-end">Net Revenue</th>
                <!-- <th class="text-end">AOV</th> -->
                <!-- <th class="text-end">Customers</th> -->
                <th class="text-end">Hours</th>
                <!-- <th class="text-end">Avg Duration (min)</th> -->
                <!-- <th>First</th>
                <th>Last</th>
                <th>Action</th> -->
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="13" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else:
                foreach($rows as $r):
                  $hrs = (float)($r['hrs'] ?? 0);
                  $avgdur = (float)($r['avgdur'] ?? 0);
              ?>
                <tr>
                  <td><?=esc($r['staff_name']?:'N/A')?></td>
                  <td class="text-end">
                    <button type="button" class="btn btn-link p-0 status-link" data-status="total" data-staff="<?= (int)$r['staff_id'] ?>" data-staff-name="<?=esc($r['staff_name']?:'N/A')?>">
                      <span class="fw-semibold"><?=number_format((int)$r['tx_total'])?></span>
                    </button>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-link p-0 status-link" data-status="confirmed" data-staff="<?= (int)$r['staff_id'] ?>" data-staff-name="<?=esc($r['staff_name']?:'N/A')?>">
                      <span class="badge bg-success"><?=number_format((int)$r['tx_conf'])?></span>
                    </button>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-link p-0 status-link" data-status="completed" data-staff="<?= (int)$r['staff_id'] ?>" data-staff-name="<?=esc($r['staff_name']?:'N/A')?>">
                      <span class="badge bg-primary"><?=number_format((int)$r['tx_comp'])?></span>
                    </button>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-link p-0 status-link" data-status="pending" data-staff="<?= (int)$r['staff_id'] ?>" data-staff-name="<?=esc($r['staff_name']?:'N/A')?>">
                      <span class="badge bg-warning text-dark"><?=number_format((int)$r['tx_pend'])?></span>
                    </button>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-link p-0 status-link" data-status="cancelled" data-staff="<?= (int)$r['staff_id'] ?>" data-staff-name="<?=esc($r['staff_name']?:'N/A')?>">
                      <span class="badge bg-danger"><?=number_format((int)$r['tx_canc'])?></span>
                    </button>
                  </td>
                  <td class="text-end fw-bold text-success">฿<?=number_format((float)$r['net_rev'],2)?></td>
                  <td class="text-end"><?=number_format($hrs,2)?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
                        <!-- <tfoot class="table-light">
              <tr>
                <th>รวม</th>
                <th class="text-end fw-bold"><?=number_format($summary['tx_total'])?></th>
                <th class="text-end"><span class="badge bg-success"><?=number_format($summary['tx_conf'])?></span></th>
                <th class="text-end"><span class="badge bg-primary"><?=number_format($summary['tx_comp'])?></span></th>
                <th class="text-end"><span class="badge bg-warning text-dark"><?=number_format($summary['tx_pend'])?></span></th>
                <th class="text-end"><span class="badge bg-secondary"><?=number_format($summary['tx_canc'])?></span></th>
                <th class="text-end fw-bold text-success">฿<?=number_format($summary['net_rev'],2)?></th>
                <th class="text-end">฿<?=number_format($summary['aov'],2)?></th>
                <th class="text-end"><?=number_format($summary['custs'])?></th>
                <th class="text-end"><?=number_format($summary['hrs'],2)?></th>
                <th class="text-end"><?=number_format($summary['avgdur'],1)?></th>
                <th class="text-muted">-</th>
                <th class="text-muted">-</th>
                <th></th>
              </tr>
            </tfoot> -->
          </table>
        </div>

        <?php if($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="text-muted">หน้า <?=number_format($page)?> / <?=number_format($pages)?></span>
          <div class="btn-group">
            <?php if($page > 1): ?>
              <a class="btn btn-outline-secondary" href="<?=esc($pageUrl($page-1))?>"><i class="bi bi-chevron-left"></i> ก่อนหน้า</a>
            <?php else: ?>
              <span class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-left"></i> ก่อนหน้า</span>
            <?php endif; ?>
            <?php if($page < $pages): ?>
              <a class="btn btn-outline-primary" href="<?=esc($pageUrl($page+1))?>">ถัดไป <i class="bi bi-chevron-right"></i></a>
            <?php else: ?>
              <span class="btn btn-outline-primary disabled">ถัดไป <i class="bi bi-chevron-right"></i></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

      </div></div>
    </div>

    <div class="modal fade" id="bookingStatusModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="bookingStatusTitle">รายการจอง</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead><tr><th>รหัส</th><th>ลูกค้า</th><th>บริการ</th><th>วันที่</th><th>เวลา</th><th class="text-end">ราคา</th></tr></thead>
                <tbody id="bookingStatusBody"><tr><td colspan="6" class="text-center text-muted">กำลังโหลด...</td></tr></tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="t-chart">
      <div class="card mt-3"><div class="card-body">

        <div class="row g-3 align-items-end">
          <div class="col-md-3 col-lg-2">
            <label class="form-label small-label" for="chart_period">ช่วงเวลา</label>
            <select class="form-select" id="chart_period">
              <option value="all">ทั้งหมด</option>
              <option value="year">ช่วงปี</option>
              <option value="month">ช่วงเดือน</option>
              <option value="day">ช่วงวัน</option>
            </select>
          </div>
          <div class="col-md-auto d-none" id="chart_year_group">
            <label class="form-label small-label">ปี (จาก-ถึง)</label>
            <div class="d-flex gap-2">
              <select class="form-select" id="chart_start_year">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
              <select class="form-select" id="chart_end_year">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto d-none" id="chart_month_group">
            <label class="form-label small-label">เดือน (จาก-ถึง)</label>
            <div class="d-flex gap-2">
              <input type="month" class="form-control" id="chart_start_month" value="<?=date('Y-m')?>">
              <input type="month" class="form-control" id="chart_end_month" value="<?=date('Y-m')?>">
            </div>
          </div>
          <div class="col-md-auto d-none" id="chart_day_group">
            <label class="form-label small-label">วันที่ (จาก-ถึง)</label>
            <div class="d-flex gap-2">
              <input type="date" class="form-control" id="chart_start_day" value="<?=date('Y-m-01')?>">
              <input type="date" class="form-control" id="chart_end_day" value="<?=date('Y-m-d')?>">
            </div>
          </div>
          <div class="col-md-auto">
            <button class="btn btn-primary" id="chartApply"><i class="bi bi-funnel"></i> ใช้ตัวกรอง</button>
          </div>
        </div>

        <div class="row mt-3 g-3">
          <div class="col-lg-8">
            <div class="card h-100"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0" id="chartTitle">ภาพรวมจำนวนการจองของสตาฟ</h6>
                <button class="btn btn-sm btn-outline-secondary d-none" id="chartBack"><i class="bi bi-arrow-left"></i> กลับ</button>
              </div>
              <div class="chart-wrapper">
                <canvas id="staffMainChart"></canvas>
              </div>
              <div class="mt-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <strong>เลือกพนักงาน</strong>
                  <button class="btn btn-sm btn-outline-primary" id="selectAllStaff" type="button">เลือกทั้งหมด</button>
                  <button class="btn btn-sm btn-outline-secondary" id="clearAllStaff" type="button">ล้างทั้งหมด</button>
                </div>
                <div id="staffCheckboxes" class="d-flex flex-wrap gap-2"></div>
              </div>
            </div></div>
          </div>
          <div class="col-lg-4">
            <div class="card h-100"><div class="card-body">
              <h6 class="card-title">Top 10 Staff</h6>
              <ol class="list-group list-group-numbered" id="topStaffList"></ol>
            </div></div>
          </div>
        </div>

        <p class="text-muted mt-3" id="chartHint">* คลิกแท่งกราฟของพนักงานเพื่อดูรายละเอียดแบบกราฟเส้น และกดปุ่ม "กลับ" เพื่อย้อนกลับ</p>

      </div></div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const filterForm = document.getElementById('staffFilterForm');
const searchInput = document.getElementById('staffSearch');
const dateTypeSelect = document.getElementById('date_filter_type');
const dateRangeGroup = document.getElementById('date_range_group');
const monthGroup = document.getElementById('month_group');
const sortField = document.getElementById('sort_field');
const sortDir = document.getElementById('sort_dir');

function toggleDateInputs() {
  const type = dateTypeSelect?.value || 'range';
  if (dateRangeGroup) dateRangeGroup.classList.toggle('d-none', type !== 'range');
  if (monthGroup) monthGroup.classList.toggle('d-none', type !== 'month');
}
toggleDateInputs();
dateTypeSelect?.addEventListener('change', toggleDateInputs);

function updateSortLabels() {
  if (!sortField || !sortDir) return;
  const asc = sortDir.querySelector('option[value="ASC"]');
  const desc = sortDir.querySelector('option[value="DESC"]');
  if (!asc || !desc) return;
  if (sortField.value === 'name') {
    asc.textContent = 'A → Z';
    desc.textContent = 'Z → A';
  } else {
    asc.textContent = 'น้อย → มาก';
    desc.textContent = 'มาก → น้อย';
  }
}
updateSortLabels();
sortField?.addEventListener('change', updateSortLabels);

let searchTimer;
if (searchInput && filterForm) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      const pageInput = filterForm.querySelector('input[name="page"]');
      if (pageInput) pageInput.remove();
      filterForm.requestSubmit();
    }, 400);
  });
}

const statusNames = {
  total: 'ทั้งหมด',
  confirmed: 'Confirmed',
  completed: 'Complate',
  pending: 'Pending',
  cancelled: 'Cancelled'
};
const bookingModalEl = document.getElementById('bookingStatusModal');
const bookingModal = bookingModalEl ? new bootstrap.Modal(bookingModalEl) : null;
const bookingBody = document.getElementById('bookingStatusBody');
const bookingTitle = document.getElementById('bookingStatusTitle');

function formatCurrency(amount) {
  return '฿' + Number(amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

async function fetchStaffBookings(staffId, statusKey, staffName) {
  if (!bookingBody || !bookingTitle) return;
  bookingTitle.textContent = `${staffName} - ${statusNames[statusKey] || ''}`;
  bookingBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">กำลังโหลด...</td></tr>';
  if (bookingModal) bookingModal.show();

  const url = new URL(location.href);
  url.search = '';
  url.searchParams.set('action', 'staff-bookings');
  url.searchParams.set('staff_id', staffId);
  url.searchParams.set('status', statusKey);
  if (filterForm) {
    const formData = new FormData(filterForm);
    for (const [key, value] of formData.entries()) {
      if (value !== '') url.searchParams.set(key, value);
    }
  }
  try {
    const res = await fetch(url.toString(), {cache: 'no-store'});
    const data = await res.json();
    const bookings = data.bookings || [];
    if (!bookings.length) {
      bookingBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูล</td></tr>';
      return;
    }
    bookingBody.innerHTML = '';
    bookings.forEach(b => {
      const tr = document.createElement('tr');
      const timeLabel = b.time_start && b.time_end ? `${b.time_start} - ${b.time_end}` : '-';
      tr.innerHTML = `
        <td>${b.id}</td>
        <td>${b.customer || '-'}</td>
        <td>${b.services || '-'}</td>
        <td>${b.date || '-'}</td>
        <td>${timeLabel}</td>
        <td class="text-end">${formatCurrency(b.price)}</td>
      `;
      bookingBody.appendChild(tr);
    });
  } catch (err) {
    console.error(err);
    bookingBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">เกิดข้อผิดพลาดในการดึงข้อมูล</td></tr>';
  }
}

document.addEventListener('click', (event) => {
  const btn = event.target.closest('.status-link');
  if (!btn) return;
  event.preventDefault();
  const staffId = btn.getAttribute('data-staff');
  const statusKey = btn.getAttribute('data-status');
  const staffName = btn.getAttribute('data-staff-name') || '';
  if (!staffId || !statusKey) return;
  fetchStaffBookings(staffId, statusKey, staffName);
});

// ---------------- Chart logic ----------------
const chartPeriodEl = document.getElementById('chart_period');
const chartYearGroup = document.getElementById('chart_year_group');
const chartMonthGroup = document.getElementById('chart_month_group');
const chartDayGroup = document.getElementById('chart_day_group');
const chartApplyBtn = document.getElementById('chartApply');
const chartTitleEl = document.getElementById('chartTitle');
const chartBackBtn = document.getElementById('chartBack');
const chartHintEl = document.getElementById('chartHint');
const selectAllBtn = document.getElementById('selectAllStaff');
const clearAllBtn = document.getElementById('clearAllStaff');
const staffCheckboxesEl = document.getElementById('staffCheckboxes');
const topStaffListEl = document.getElementById('topStaffList');
const chartCanvas = document.getElementById('staffMainChart');
let staffChart = null;
let overviewData = [];
let selectedStaffIds = new Set();
let detailMode = false;
let currentDetail = null;

function updateChartPeriodControls() {
  const period = chartPeriodEl?.value || 'all';
  chartYearGroup?.classList.toggle('d-none', period !== 'year');
  chartMonthGroup?.classList.toggle('d-none', period !== 'month');
  chartDayGroup?.classList.toggle('d-none', period !== 'day');
}
updateChartPeriodControls();
chartPeriodEl?.addEventListener('change', updateChartPeriodControls);

function gatherChartFilters() {
  const period = chartPeriodEl?.value || 'all';
  const filters = { period };
  if (period === 'year') {
    filters.start_year = document.getElementById('chart_start_year')?.value || '';
    filters.end_year = document.getElementById('chart_end_year')?.value || '';
  } else if (period === 'month') {
    filters.start_month = document.getElementById('chart_start_month')?.value || '';
    filters.end_month = document.getElementById('chart_end_month')?.value || '';
  } else if (period === 'day') {
    filters.start_date = document.getElementById('chart_start_day')?.value || '';
    filters.end_date = document.getElementById('chart_end_day')?.value || '';
  }
  return filters;
}

function buildChartUrl(action, extra = {}) {
  const url = new URL(location.href);
  url.search = '';
  url.searchParams.set('action', action);
  const filters = gatherChartFilters();
  Object.entries({...filters, ...extra}).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, value);
    }
  });
  return url;
}

function renderTopList(items) {
  if (!topStaffListEl) return;
  topStaffListEl.innerHTML = '';
  if (!items || !items.length) {
    const li = document.createElement('li');
    li.className = 'list-group-item text-muted';
    li.textContent = 'ไม่มีข้อมูล';
    topStaffListEl.appendChild(li);
    return;
  }
  items.forEach((item) => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    const total = Number(item.total || 0).toLocaleString();
    li.innerHTML = `<span>${item.name || '-'}</span><span class="badge bg-primary rounded-pill">${total} ครั้ง</span>`;
    topStaffListEl.appendChild(li);
  });
}

function renderStaffCheckboxes() {
  if (!staffCheckboxesEl) return;
  staffCheckboxesEl.innerHTML = '';
  if (!overviewData.length) {
    staffCheckboxesEl.innerHTML = '<span class="text-muted">ไม่มีข้อมูล</span>';
    return;
  }
  overviewData.forEach((staff) => {
    const wrapper = document.createElement('label');
    wrapper.className = 'form-check form-check-inline';
    const checked = selectedStaffIds.has(staff.id) ? 'checked' : '';
    wrapper.innerHTML = `
      <input class="form-check-input staff-filter" type="checkbox" value="${staff.id}" ${checked}>
      <span class="form-check-label">${staff.name}</span>
    `;
    staffCheckboxesEl.appendChild(wrapper);
  });
  staffCheckboxesEl.querySelectorAll('.staff-filter').forEach((input) => {
    input.addEventListener('change', () => {
      const id = Number(input.value);
      if (input.checked) {
        selectedStaffIds.add(id);
      } else {
        selectedStaffIds.delete(id);
      }
      detailMode = false;
      currentDetail = null;
      chartBackBtn?.classList.add('d-none');
      chartTitleEl.textContent = 'ภาพรวมจำนวนการจองของสตาฟ';
      renderOverviewChart();
    });
  });
}

function renderOverviewChart() {
  if (!chartCanvas) return;
  const ctx = chartCanvas.getContext('2d');
  if (staffChart) {
    staffChart.destroy();
    staffChart = null;
  }
  let staffList;
  if (selectedStaffIds.size === 0) {
    staffList = [];
  } else {
    staffList = overviewData.filter((staff) => selectedStaffIds.has(staff.id));
  }
  if (!staffList.length) {
    if (chartHintEl) chartHintEl.textContent = 'กรุณาเลือกพนักงานเพื่อแสดงข้อมูล';
    return;
  }
  const labels = staffList.map((s) => s.name);
  const palette = {
    total: '#0d6efd',
    confirmed: '#198754',
    completed: '#0dcaf0',
    pending: '#ffc107',
    cancelled: '#dc3545'
  };
  staffChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {label: 'ทั้งหมด', data: staffList.map(s => Number(s.total || 0)), backgroundColor: palette.total},
        {label: 'Confirmed', data: staffList.map(s => Number(s.confirmed || 0)), backgroundColor: palette.confirmed},
        {label: 'Complate', data: staffList.map(s => Number(s.completed || 0)), backgroundColor: palette.completed},
        {label: 'Pending', data: staffList.map(s => Number(s.pending || 0)), backgroundColor: palette.pending},
        {label: 'Cancelled', data: staffList.map(s => Number(s.cancelled || 0)), backgroundColor: palette.cancelled}
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true } },
      plugins: {
        legend: { display: true },
        tooltip: { mode: 'index', intersect: false }
      },
      onClick(evt, elements) {
        if (!elements.length) return;
        const idx = elements[0].index;
        const staff = staffList[idx];
        if (staff) loadDetail(staff.id, staff.name);
      }
    }
  });
  if (chartHintEl) {
    chartHintEl.textContent = '* คลิกแท่งกราฟของพนักงานเพื่อดูรายละเอียดแบบกราฟเส้น และกดปุ่ม "กลับ" เพื่อย้อนกลับ';
  }
}

async function loadOverview() {
  const url = buildChartUrl('chart_overview');
  try {
    const res = await fetch(url.toString(), {cache: 'no-store'});
    const data = await res.json();
    overviewData = data.staffs || [];
    detailMode = false;
    currentDetail = null;
    chartBackBtn?.classList.add('d-none');
    chartTitleEl.textContent = 'ภาพรวมจำนวนการจองของสตาฟ';
    if (!overviewData.length) {
      selectedStaffIds.clear();
      renderStaffCheckboxes();
      if (chartHintEl) chartHintEl.textContent = 'ไม่มีข้อมูลพนักงาน';
      if (staffChart) { staffChart.destroy(); staffChart = null; }
      renderTopList([]);
      return;
    }
    selectedStaffIds = new Set(overviewData.map((s) => s.id));
    renderStaffCheckboxes();
    renderOverviewChart();
    renderTopList(data.top || []);
  } catch (err) {
    console.error(err);
    if (chartHintEl) chartHintEl.textContent = 'ไม่สามารถโหลดข้อมูลกราฟได้';
  }
}

async function loadDetail(staffId, staffName) {
  const url = buildChartUrl('chart_staff_detail', {staff_id: staffId});
  try {
    const res = await fetch(url.toString(), {cache: 'no-store'});
    const data = await res.json();
    const labels = data.labels || [];
    const series = data.series || {};
    const ctx = chartCanvas.getContext('2d');
    if (staffChart) {
      staffChart.destroy();
      staffChart = null;
    }
    const palette = {
      total: '#0d6efd',
      confirmed: '#198754',
      completed: '#0dcaf0',
      pending: '#ffc107',
      cancelled: '#dc3545'
    };
    staffChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {label: 'ทั้งหมด', data: (series.total || []).map(v => Number(v || 0)), borderColor: palette.total, backgroundColor: palette.total, fill: false, tension: 0.3, pointRadius: 3},
          {label: 'Confirmed', data: (series.confirmed || []).map(v => Number(v || 0)), borderColor: palette.confirmed, backgroundColor: palette.confirmed, fill: false, tension: 0.3, pointRadius: 3},
          {label: 'Complate', data: (series.completed || []).map(v => Number(v || 0)), borderColor: palette.completed, backgroundColor: palette.completed, fill: false, tension: 0.3, pointRadius: 3},
          {label: 'Pending', data: (series.pending || []).map(v => Number(v || 0)), borderColor: palette.pending, backgroundColor: palette.pending, fill: false, tension: 0.3, pointRadius: 3},
          {label: 'Cancelled', data: (series.cancelled || []).map(v => Number(v || 0)), borderColor: palette.cancelled, backgroundColor: palette.cancelled, fill: false, tension: 0.3, pointRadius: 3}
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } },
        plugins: {
          legend: { display: true },
          tooltip: { mode: 'index', intersect: false }
        }
      }
    });
    detailMode = true;
    currentDetail = {staffId, staffName};
    chartBackBtn?.classList.remove('d-none');
    chartTitleEl.textContent = `รายละเอียดของ ${staffName}`;
    if (chartHintEl) chartHintEl.textContent = 'กำลังแสดงกราฟเส้นของพนักงานที่เลือก';
  } catch (err) {
    console.error(err);
  }
}

chartApplyBtn?.addEventListener('click', (event) => {
  event.preventDefault();
  loadOverview();
});

chartBackBtn?.addEventListener('click', () => {
  detailMode = false;
  currentDetail = null;
  chartBackBtn.classList.add('d-none');
  chartTitleEl.textContent = 'ภาพรวมจำนวนการจองของสตาฟ';
  renderOverviewChart();
});

selectAllBtn?.addEventListener('click', () => {
  selectedStaffIds = new Set(overviewData.map((s) => s.id));
  detailMode = false;
  currentDetail = null;
  chartBackBtn?.classList.add('d-none');
  chartTitleEl.textContent = 'ภาพรวมจำนวนการจองของสตาฟ';
  renderStaffCheckboxes();
  renderOverviewChart();
});

clearAllBtn?.addEventListener('click', () => {
  selectedStaffIds.clear();
  detailMode = false;
  currentDetail = null;
  chartBackBtn?.classList.add('d-none');
  chartTitleEl.textContent = 'ภาพรวมจำนวนการจองของสตาฟ';
  renderStaffCheckboxes();
  if (chartHintEl) chartHintEl.textContent = 'กรุณาเลือกพนักงานเพื่อแสดงข้อมูล';
  if (staffChart) {
    staffChart.destroy();
    staffChart = null;
  }
});

const chartTabBtn = document.getElementById('chart-tab');
if (chartTabBtn) {
  chartTabBtn.addEventListener('shown.bs.tab', () => {
    if (!overviewData.length) {
      loadOverview();
    }
  });
}
</script>
</body>
</html>