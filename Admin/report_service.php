<?php
// report_service.php — Service Report (2 Tabs: Table + Chart)
// - KPI cards (GLOBAL; not affected by chart filters)
// - Table: aggregate by Service (Tx, Customers, Gross, Discount, Net, Last Booking), filters + sort + pagination
// - Chart: time-series by service (multi-line) with period All/Year/Month, metric Net/Bookings, Top N or pick a single service
// Schema expected:
//   booking(booking_id, booking_date, time_start, time_end, customer_id, staff_id, status, total_price, total_discount, final_price)
//   booking_seviceop(booking_id, option_id, price_booking, discount_booking, net_price)  -- note: original project spells "sevice"
//   service_option(option_id, service_id, price)
//   service(service_id, service_name)
//   customer(customer_id, ...), staff(staff_id, ...)

require_once("connect_db.php");
$confirmedStatus = BOOKING_STATUS_CONFIRMED;
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function normalize_date(string $value): ?string {
  $value = trim($value);
  if ($value === '') { return null; }
  $dt = date_create($value);
  return $dt ? $dt->format('Y-m-d') : null;
}

function month_range_to_dates(int $monthStart, int $yearStart, int $monthEnd, int $yearEnd): array {
  $start = date_create(sprintf('%04d-%02d-01', $yearStart, $monthStart));
  $end   = date_create(sprintf('%04d-%02d-01', $yearEnd, $monthEnd));
  if (!$start || !$end) { return [null, null]; }
  if ($start > $end) { [$start, $end] = [$end, $start]; }
  $end->modify('last day of this month');
  return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}

function year_range_to_dates(int $yearStart, int $yearEnd): array {
  if ($yearStart > $yearEnd) { [$yearStart, $yearEnd] = [$yearEnd, $yearStart]; }
  return [sprintf('%04d-01-01', $yearStart), sprintf('%04d-12-31', $yearEnd)];
}

function resolve_period_range(string $period, array $input, int $minYear, int $maxYear): array {
  $period = in_array($period, ['all','year','month','day'], true) ? $period : 'all';
  $start = null; $end = null;
  if ($period === 'day') {
    $start = normalize_date($input['day_start'] ?? '');
    $end   = normalize_date($input['day_end'] ?? '');
    if ($start && !$end) { $end = $start; }
    if ($end && !$start) { $start = $end; }
    if ($start && $end && $start > $end) { [$start, $end] = [$end, $start]; }
  } elseif ($period === 'month') {
    $sm = (int)($input['month_start'] ?? date('n'));
    $sy = max($minYear, min($maxYear, (int)($input['month_start_year'] ?? date('Y'))));
    $em = (int)($input['month_end'] ?? $sm);
    $ey = max($minYear, min($maxYear, (int)($input['month_end_year'] ?? $sy)));
    [$start, $end] = month_range_to_dates(max(1,min(12,$sm)), $sy, max(1,min(12,$em)), $ey);
  } elseif ($period === 'year') {
    $sy = max($minYear, min($maxYear, (int)($input['year_start'] ?? date('Y'))));
    $ey = max($minYear, min($maxYear, (int)($input['year_end'] ?? $sy)));
    [$start, $end] = year_range_to_dates($sy, $ey);
  }
  return [$period, $start, $end];
}

// ----------------------- Year range meta for pickers -----------------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ======================= AJAX: KPIs + Chart =======================
if (isset($_GET['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  if ($_GET['action'] === 'stats') {
    [$period, $rangeStart, $rangeEnd] = resolve_period_range($_GET['chart_period'] ?? 'all', $_GET, $minYear, $maxYear);
    $dateCond = '';
    if ($rangeStart) { $dateCond .= " AND b.booking_date >= '".$conn->real_escape_string($rangeStart)."'"; }
    if ($rangeEnd)   { $dateCond .= " AND b.booking_date <= '".$conn->real_escape_string($rangeEnd)."'"; }

    // ---------- KPI (GLOBAL; confirmed bookings only) ----------
    $k = $conn->query("
      SELECT
        COALESCE(SUM(final_price),0)   AS net,
        COUNT(*)                       AS tx,
        COUNT(DISTINCT customer_id)    AS customers
      FROM booking
      WHERE status={$confirmedStatus}
    ")->fetch_assoc();
    $net_total = (float)$k['net'];
    $tx_total = (int)$k['tx'];
    $cust_total = (int)$k['customers'];
    $svc_total = (int)$conn->query("
      SELECT COUNT(DISTINCT so.service_id) c
      FROM booking b
      JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      JOIN service_option so ON so.option_id=bs.option_id
      WHERE b.status={$confirmedStatus}
    ")->fetch_assoc()['c'];
    $top = $conn->query("
      SELECT sv.service_name, COALESCE(SUM(COALESCE(bs.net_price, bs.price_booking-COALESCE(bs.discount_booking,0), so.price)),0) AS net
      FROM booking b
      JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      JOIN service_option so ON so.option_id=bs.option_id
      JOIN service sv ON sv.service_id=so.service_id
      WHERE b.status={$confirmedStatus}
      GROUP BY sv.service_id
      ORDER BY net DESC
      LIMIT 1
    ")->fetch_assoc();
    $top_service = $top['service_name'] ?? '-';
    $top_service_net = (float)($top['net'] ?? 0);

    $sqlServices = "
      SELECT
        sv.service_id,
        sv.service_name,
        COUNT(DISTINCT CASE WHEN b.status={$confirmedStatus}{$dateCond} THEN b.booking_id END) AS total_booking
      FROM service sv
      LEFT JOIN service_option so ON so.service_id = sv.service_id
      LEFT JOIN booking_seviceop bs ON bs.option_id = so.option_id
      LEFT JOIN booking b ON b.booking_id = bs.booking_id
      GROUP BY sv.service_id, sv.service_name
    ";
    $services = [];
    $rs = $conn->query($sqlServices);
    while($row = $rs->fetch_assoc()){
      $row['service_id'] = (int)$row['service_id'];
      $row['total_booking'] = (int)($row['total_booking'] ?? 0);
      $services[] = $row;
    }
    $bars = $services;
    usort($bars, function($a,$b){
      return ($b['total_booking'] <=> $a['total_booking']) ?: strcasecmp($a['service_name'],$b['service_name']);
    });
    $allList = $services;
    usort($allList, function($a,$b){ return strcasecmp($a['service_name'],$b['service_name']); });
    $top10 = array_slice($bars, 0, 10);

    echo json_encode([
      'cards'=>[
        'net_total'=>$net_total,
        'tx_total'=>$tx_total,
        'customers_total'=>$cust_total,
        'services_sold'=>$svc_total,
        'top_service'=>$top_service,
        'top_service_net'=>$top_service_net
      ],
      'chart'=>[
        'period'=>$period,
        'range'=>['start'=>$rangeStart,'end'=>$rangeEnd],
        'bars'=>$bars,
        'services'=>$allList,
        'top10'=>$top10
      ]
    ]);
    exit;
  }

  if ($_GET['action'] === 'timeline') {
    $serviceId = (int)($_GET['service_id'] ?? 0);
    if ($serviceId <= 0) {
      echo json_encode(['error'=>'invalid service']);
      exit;
    }
    [$period, $rangeStart, $rangeEnd] = resolve_period_range($_GET['chart_period'] ?? 'all', $_GET, $minYear, $maxYear);

    $svcRow = $conn->query("SELECT service_name FROM service WHERE service_id={$serviceId} LIMIT 1")->fetch_assoc();
    $serviceName = $svcRow['service_name'] ?? '';

    if (!$rangeStart || !$rangeEnd) {
      $rangeRow = $conn->query("
        SELECT MIN(b.booking_date) AS min_d, MAX(b.booking_date) AS max_d
        FROM booking b
        JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
        JOIN service_option so ON so.option_id=bs.option_id
        WHERE b.status={$confirmedStatus} AND so.service_id={$serviceId}
      ")->fetch_assoc();
      $rangeStart = $rangeRow['min_d'] ?? null;
      $rangeEnd   = $rangeRow['max_d'] ?? null;
    }
    if (!$rangeStart || !$rangeEnd) {
      echo json_encode([
        'service'=>$serviceName,
        'labels'=>[],
        'data'=>[],
        'axis'=>'',
        'range'=>['start'=>$rangeStart,'end'=>$rangeEnd,'period'=>$period]
      ]);
      exit;
    }

    $labels=[]; $bucketExpr=''; $axis='';
    if ($period === 'all') {
      $startYear = (int)substr($rangeStart,0,4);
      $endYear   = (int)substr($rangeEnd,0,4);
      if ($startYear > $endYear) { [$startYear,$endYear] = [$endYear,$startYear]; }
      for($y=$startYear;$y<=$endYear;$y++){ $labels[] = (string)$y; }
      $bucketExpr = "YEAR(b.booking_date)";
      $axis = 'ปี';
    } elseif ($period === 'year') {
      $start = date_create($rangeStart);
      $start->modify('first day of this month');
      $end = date_create($rangeEnd);
      $end->modify('first day of next month');
      while($start < $end){
        $labels[] = $start->format('Y-m');
        $start->modify('+1 month');
      }
      $bucketExpr = "DATE_FORMAT(b.booking_date,'%Y-%m')";
      $axis = 'เดือน';
    } else {
      $start = date_create($rangeStart);
      $end = date_create($rangeEnd);
      $end->modify('+1 day');
      while($start < $end){
        $labels[] = $start->format('Y-m-d');
        $start->modify('+1 day');
      }
      $bucketExpr = "DATE_FORMAT(b.booking_date,'%Y-%m-%d')";
      $axis = 'วัน';
    }

    $dateCond = " AND b.booking_date >= '".$conn->real_escape_string($rangeStart)."' AND b.booking_date <= '".$conn->real_escape_string($rangeEnd)."'";
    $sqlTimeline = "
      SELECT {$bucketExpr} AS bucket, COUNT(DISTINCT b.booking_id) AS total
      FROM booking b
      JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      JOIN service_option so ON so.option_id=bs.option_id
      WHERE b.status={$confirmedStatus}{$dateCond} AND so.service_id={$serviceId}
      GROUP BY bucket
      ORDER BY bucket
    ";
    $map=[]; $rs=$conn->query($sqlTimeline);
    while($row=$rs->fetch_assoc()){ $map[(string)$row['bucket']] = (int)$row['total']; }
    $data=[]; foreach($labels as $label){ $data[] = (int)($map[$label] ?? 0); }

    echo json_encode([
      'service'=>$serviceName,
      'labels'=>$labels,
      'data'=>$data,
      'axis'=>$axis,
      'range'=>['start'=>$rangeStart,'end'=>$rangeEnd,'period'=>$period]
    ]);
    exit;
  }

  echo json_encode(['error'=>'unsupported action']);
  exit;
}

// ======================= TABLE: filters + sort + pagination =======================
$tableSearch = trim($_GET['q'] ?? '');
$tableStatus = $_GET['service_status'] ?? 'all';
[$tablePeriod, $tableRangeStart, $tableRangeEnd] = resolve_period_range($_GET['table_period'] ?? 'all', $_GET, $minYear, $maxYear);
$tableSortField = $_GET['sort_field'] ?? 'popularity';
$rawDir = strtoupper($_GET['sort_dir'] ?? '');
if ($rawDir !== 'ASC' && $rawDir !== 'DESC') {
  $tableSortDir = $tableSortField === 'name' ? 'ASC' : 'DESC';
} else {
  $tableSortDir = $rawDir;
}
$tableSortDir = $tableSortDir === 'ASC' ? 'ASC' : 'DESC';

$tableDateCond = '';
if ($tableRangeStart) { $tableDateCond .= " AND b.booking_date >= '".$conn->real_escape_string($tableRangeStart)."'"; }
if ($tableRangeEnd)   { $tableDateCond .= " AND b.booking_date <= '".$conn->real_escape_string($tableRangeEnd)."'"; }

$whereParts = ['1=1'];
$types = '';
$params = [];
if ($tableSearch !== '') {
  $isNumeric = ctype_digit($tableSearch);
  if ($isNumeric) {
    $whereParts[] = '(sv.service_name LIKE ? OR sv.service_id = ?)';
    $types .= 'si';
    $params[] = '%' . $tableSearch . '%';
    $params[] = (int)$tableSearch;
  } else {
    $whereParts[] = 'sv.service_name LIKE ?';
    $types .= 's';
    $params[] = '%' . $tableSearch . '%';
  }
}
if ($tableStatus === 'active') {
  $whereParts[] = 'sv.is_active = 1';
} elseif ($tableStatus === 'inactive') {
  $whereParts[] = 'sv.is_active = 0';
}
$whereSql = implode(' AND ', $whereParts);

$sortMap = [
  'name' => 'sv.service_name',
  'popularity' => 'total_booking',
  'price' => 'base_price',
  'option' => 'option_count'
];
$orderColumn = $sortMap[$tableSortField] ?? $sortMap['popularity'];
$orderBy = $orderColumn . ' ' . $tableSortDir;

$sqlCount = "SELECT COUNT(*) AS cnt FROM service sv WHERE $whereSql";
$stmt = $conn->prepare($sqlCount);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$total = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

$per = 20;
$pages = max(1, (int)ceil($total / $per));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$off = ($page - 1) * $per;

$sqlList = "
  SELECT
    sv.service_id,
    sv.service_name,
    sv.is_active,
    COUNT(DISTINCT so.option_id) AS option_count,
    MIN(so.price) AS base_price,
    COUNT(DISTINCT CASE WHEN b.status={$confirmedStatus}{$tableDateCond} THEN b.booking_id END) AS total_booking
  FROM service sv
  LEFT JOIN service_option so ON so.service_id = sv.service_id
  LEFT JOIN booking_seviceop bs ON bs.option_id = so.option_id
  LEFT JOIN booking b ON b.booking_id = bs.booking_id
  WHERE $whereSql
  GROUP BY sv.service_id, sv.service_name, sv.is_active
  ORDER BY $orderBy
  LIMIT ? OFFSET ?
";
$paramsList = $params;
$typesList = $types . 'ii';
$paramsList[] = $per;
$paramsList[] = $off;
$stmt = $conn->prepare($sqlList);
if (!empty($paramsList)) { $stmt->bind_param($typesList, ...$paramsList); }
$stmt->execute();
$rs = $stmt->get_result();
$rows = [];
$totalBookingSum = 0;
while($r = $rs->fetch_assoc()){
  $r['option_count'] = (int)($r['option_count'] ?? 0);
  $r['total_booking'] = (int)($r['total_booking'] ?? 0);
  $r['base_price'] = $r['base_price'] !== null ? (float)$r['base_price'] : null;
  $rows[] = $r;
  $totalBookingSum += $r['total_booking'];
}
$stmt->close();

$baseQuery = $_GET;
unset($baseQuery['page'], $baseQuery['tab'], $baseQuery['action']);
$baseQuery['tab'] = 'table';
$pageUrl = function(int $target) use ($baseQuery): string {
  $query = $baseQuery;
  $query['page'] = $target;
  return '?' . http_build_query($query);
};

$tableDayStartVal = $tablePeriod === 'day' ? ($tableRangeStart ?? '') : '';
$tableDayEndVal   = $tablePeriod === 'day' ? ($tableRangeEnd ?? '') : '';
$tableMonthStartMonth = (int)date('n');
$tableMonthStartYear  = (int)date('Y');
$tableMonthEndMonth   = $tableMonthStartMonth;
$tableMonthEndYear    = $tableMonthStartYear;
if ($tablePeriod === 'month' && $tableRangeStart) {
  $ds = date_create($tableRangeStart);
  if ($ds) {
    $tableMonthStartMonth = (int)$ds->format('n');
    $tableMonthStartYear  = (int)$ds->format('Y');
  }
}
if ($tablePeriod === 'month' && $tableRangeEnd) {
  $de = date_create($tableRangeEnd);
  if ($de) {
    $tableMonthEndMonth = (int)$de->format('n');
    $tableMonthEndYear  = (int)$de->format('Y');
  }
}
$tableYearStartVal = $tablePeriod === 'year' && $tableRangeStart ? (int)substr($tableRangeStart,0,4) : $minYear;
$tableYearEndVal   = $tablePeriod === 'year' && $tableRangeEnd   ? (int)substr($tableRangeEnd,0,4)   : $maxYear;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Service Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    .small-label{font-size:.9rem;color:#6c757d}
    .kpi-value{font-size:28px;font-weight:700;margin:0}
    .card-icon{font-size:28px}
    .table-sm td, .table-sm th { padding: .45rem .6rem; }
  </style>
</head>
<body>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>
<main id="main" class="main">

  <div class="pagetitle"><h1>รายงานบริการ (Service Report)</h1></div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-table" type="button">ตาราง</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-chart" type="button" id="chart-tab">กราฟ</button></li>
  </ul>

  <div class="tab-content">
    <!-- TAB: TABLE -->
    <div class="tab-pane fade show active" id="t-table">
      <div class="card mt-3"><div class="card-body">

        <form class="row g-3 align-items-end" method="get" id="tableFilters">
          <input type="hidden" name="tab" value="table">
          <div class="col-lg-3 col-md-4">
            <label class="form-label small-label">ค้นหา (ID หรือชื่อบริการ)</label>
            <input type="text" class="form-control" name="q" value="<?=esc($tableSearch)?>" placeholder="ค้นหาบริการ" data-autosubmit>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">สถานะบริการ</label>
            <select class="form-select" name="service_status">
              <option value="all" <?= $tableStatus==='all'?'selected':'' ?>>ทั้งหมด</option>
              <option value="active" <?= $tableStatus==='active'?'selected':'' ?>>เปิดใช้งาน</option>
              <option value="inactive" <?= $tableStatus==='inactive'?'selected':'' ?>>ปิดใช้งาน</option>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">ช่วงข้อมูล</label>
            <select class="form-select" name="table_period" id="tablePeriod">
              <option value="all" <?= $tablePeriod==='all'?'selected':'' ?>>ทั้งหมด</option>
              <option value="day" <?= $tablePeriod==='day'?'selected':'' ?>>ช่วงวัน</option>
              <option value="month" <?= $tablePeriod==='month'?'selected':'' ?>>ช่วงเดือน</option>
              <option value="year" <?= $tablePeriod==='year'?'selected':'' ?>>ช่วงปี</option>
            </select>
          </div>
          <div class="col-lg-4 col-md-6 period-control <?= $tablePeriod==='day'?'':'d-none' ?>" id="tablePeriodDay">
            <label class="form-label small-label">เลือกวัน</label>
            <div class="d-flex flex-wrap gap-2">
              <input type="date" class="form-control" name="day_start" value="<?=esc($tableDayStartVal)?>">
              <span class="align-self-center small text-muted">ถึง</span>
              <input type="date" class="form-control" name="day_end" value="<?=esc($tableDayEndVal)?>">
            </div>
          </div>
          <div class="col-lg-5 col-md-6 period-control <?= $tablePeriod==='month'?'':'d-none' ?>" id="tablePeriodMonth">
            <label class="form-label small-label">เลือกเดือน</label>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="d-flex gap-2">
                <select class="form-select" name="month_start">
                  <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?= $tableMonthStartMonth===$m?'selected':'' ?>><?=sprintf('%02d',$m)?></option>
                  <?php endfor; ?>
                </select>
                <select class="form-select" name="month_start_year">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                    <option value="<?=$y?>" <?= $tableMonthStartYear===$y?'selected':'' ?>><?=$y?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <span class="small text-muted">ถึง</span>
              <div class="d-flex gap-2">
                <select class="form-select" name="month_end">
                  <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?= $tableMonthEndMonth===$m?'selected':'' ?>><?=sprintf('%02d',$m)?></option>
                  <?php endfor; ?>
                </select>
                <select class="form-select" name="month_end_year">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                    <option value="<?=$y?>" <?= $tableMonthEndYear===$y?'selected':'' ?>><?=$y?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-auto period-control <?= $tablePeriod==='year'?'':'d-none' ?>" id="tablePeriodYear">
            <label class="form-label small-label">ช่วงปี</label>
            <div class="d-flex gap-2">
              <select class="form-select" name="year_start">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $tableYearStartVal===$y?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
              <select class="form-select" name="year_end">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $tableYearEndVal===$y?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <label class="form-label small-label">Sort by</label>
            <div class="input-group">
              <select class="form-select" name="sort_field" id="sortField">
                <option value="name" <?= $tableSortField==='name'?'selected':'' ?>>ชื่อ</option>
                <option value="popularity" <?= $tableSortField==='popularity'?'selected':'' ?>>ความนิยม (จำนวนจอง)</option>
                <option value="price" <?= $tableSortField==='price'?'selected':'' ?>>ราคาเริ่มต้น</option>
                <option value="option" <?= $tableSortField==='option'?'selected':'' ?>>จำนวนออปชัน</option>
              </select>
              <select class="form-select" name="sort_dir" id="sortDir">
                <option value="ASC" <?= $tableSortDir==='ASC'?'selected':'' ?>>ASC</option>
                <option value="DESC" <?= $tableSortDir==='DESC'?'selected':'' ?>>DESC</option>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i> ใช้ตัวกรอง</button>
          </div>
          <div class="ms-auto col-md-auto d-flex gap-2 align-items-center">
            <span class="badge bg-primary">บริการที่พบ: <?=number_format($total)?></span>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Service</th>
                <th>สถานะ</th>
                <th class="text-center">Option</th>
                <th class="text-end">ราคาเริ่มต้น</th>
                <th class="text-end">Total Booking</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $r): ?>
                <tr>
                  <td><?=number_format((int)$r['service_id'])?></td>
                  <td><?=esc($r['service_name'])?></td>
                  <td>
                    <?php if ((int)$r['is_active'] === 1): ?>
                      <span class="badge bg-success-subtle text-success">เปิดใช้งาน</span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary">ปิดใช้งาน</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center"><?=number_format($r['option_count'])?></td>
                  <td class="text-end"><?= $r['base_price'] !== null ? number_format($r['base_price'],2) : '-' ?></td>
                  <td class="text-end fw-semibold"><?=number_format($r['total_booking'])?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <tfoot class="table-light">
              <tr>
                <th colspan="5" class="text-end">รวมการจอง</th>
                <th class="text-end"><?=number_format($totalBookingSum)?></th>
              </tr>
            </tfoot>
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

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="t-chart">
      <div class="card mt-3"><div class="card-body">

        <!-- Global KPI (not filtered) -->
        <div class="row g-3">
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-cash-coin card-icon text-success"></i>
            <div><div class="kpi-value" id="k_net_total">0</div><div class="text-muted">Net รวม (ยืนยัน)</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-cart-check card-icon text-primary"></i>
            <div><div class="kpi-value" id="k_tx_total">0</div><div class="text-muted">Transactions</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-person-check card-icon text-secondary"></i>
            <div><div class="kpi-value" id="k_customers_total">0</div><div class="text-muted">Customers</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-stars card-icon text-warning"></i>
            <div><div class="kpi-value" id="k_services_total">0</div><div class="text-muted">บริการที่ขายแล้ว</div></div>
          </div></div></div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-6"><div class="alert alert-light py-2 m-0"><small>Top Service: <b id="k_top_service">-</b> — Net <b id="k_top_service_net">0.00</b></small></div></div>
        </div>
        <div class="text-muted mb-2">* การ์ดไม่ใช้ตัวกรอง</div>

        <!-- Chart Filters -->
        <div class="row g-3 align-items-end" id="chartFilters">
          <div class="col-md-auto">
            <label class="form-label small-label">ช่วงข้อมูล</label>
            <select class="form-select" id="chartPeriod">
              <option value="all">ทั้งหมด</option>
              <option value="year">ช่วงปี</option>
              <option value="month">ช่วงเดือน</option>
              <option value="day">ช่วงวัน</option>
            </select>
          </div>
          <div class="col-lg-4 col-md-6 chart-period-control d-none" id="chartPeriodDay">
            <label class="form-label small-label">เลือกวัน</label>
            <div class="d-flex flex-wrap gap-2">
              <input type="date" class="form-control" id="chartDayStart">
              <span class="align-self-center small text-muted">ถึง</span>
              <input type="date" class="form-control" id="chartDayEnd">
            </div>
          </div>
          <div class="col-lg-5 col-md-6 chart-period-control d-none" id="chartPeriodMonth">
            <label class="form-label small-label">เลือกเดือน</label>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="d-flex gap-2">
                <select class="form-select" id="chartMonthStart">
                  <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?= $m==(int)date('n')?'selected':'' ?>><?=sprintf('%02d',$m)?></option>
                  <?php endfor; ?>
                </select>
                <select class="form-select" id="chartMonthStartYear">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                    <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <span class="small text-muted">ถึง</span>
              <div class="d-flex gap-2">
                <select class="form-select" id="chartMonthEnd">
                  <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?= $m==(int)date('n')?'selected':'' ?>><?=sprintf('%02d',$m)?></option>
                  <?php endfor; ?>
                </select>
                <select class="form-select" id="chartMonthEndYear">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                    <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-auto chart-period-control d-none" id="chartPeriodYear">
            <label class="form-label small-label">ช่วงปี</label>
            <div class="d-flex gap-2">
              <select class="form-select" id="chartYearStart">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
              <select class="form-select" id="chartYearEnd">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button id="applyChart" class="btn btn-primary"><i class="bi bi-funnel"></i> ใช้ตัวกรอง</button>
          </div>
        </div>

        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">เลือกบริการที่ต้องการเปรียบเทียบ</h6>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="chartSelectAll">เลือกทั้งหมด</button>
          </div>
          <div class="mt-2 d-flex flex-wrap gap-2" id="chartServiceCheckboxes"></div>
        </div>

        <!-- Chart -->
        <div class="row mt-4 g-4">
          <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0" id="chartDetailTitle">กราฟจำนวนการจองต่อบริการ</h5>
              <button class="btn btn-sm btn-outline-secondary d-none" type="button" id="chartBackButton"><i class="bi bi-arrow-left"></i> ย้อนกลับ</button>
            </div>
            <div class="bg-light rounded p-2" style="min-height:420px">
              <canvas id="svcChart" height="140"></canvas>
            </div>
            <div class="text-muted mt-2 small">* คลิกแท่งกราฟเพื่อดูรายละเอียดแบบกราฟเส้นของบริการนั้น ๆ</div>
          </div>
          <div class="col-lg-3">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Top 10 Service</h6>
                <ol class="list-group list-group-numbered" id="topServiceList"></ol>
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
const tablePeriodSelect = document.getElementById('tablePeriod');
function updateTablePeriodControls(){
  const period = tablePeriodSelect?.value || 'all';
  document.getElementById('tablePeriodDay')?.classList.toggle('d-none', period !== 'day');
  document.getElementById('tablePeriodMonth')?.classList.toggle('d-none', period !== 'month');
  document.getElementById('tablePeriodYear')?.classList.toggle('d-none', period !== 'year');
}
tablePeriodSelect?.addEventListener('change', updateTablePeriodControls);
updateTablePeriodControls();

const searchInput = document.querySelector('[data-autosubmit]');
if (searchInput) {
  let timer;
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const form = document.getElementById('tableFilters');
      if (form) form.submit();
    }, 400);
  });
}

const sortField = document.getElementById('sortField');
const sortDir = document.getElementById('sortDir');
function updateSortDirLabels(){
  if (!sortDir) return;
  const field = sortField?.value || 'name';
  const ascOpt = sortDir.querySelector('option[value="ASC"]');
  const descOpt = sortDir.querySelector('option[value="DESC"]');
  if (!ascOpt || !descOpt) return;
  if (field === 'name') {
    ascOpt.textContent = 'A - Z';
    descOpt.textContent = 'Z - A';
  } else {
    ascOpt.textContent = 'น้อยไปมาก';
    descOpt.textContent = 'มากไปน้อย';
  }
}
sortField?.addEventListener('change', updateSortDirLabels);
updateSortDirLabels();

const chartPeriodSelect = document.getElementById('chartPeriod');
function updateChartPeriodControls(){
  const period = chartPeriodSelect?.value || 'all';
  document.getElementById('chartPeriodDay')?.classList.toggle('d-none', period !== 'day');
  document.getElementById('chartPeriodMonth')?.classList.toggle('d-none', period !== 'month');
  document.getElementById('chartPeriodYear')?.classList.toggle('d-none', period !== 'year');
}
chartPeriodSelect?.addEventListener('change', updateChartPeriodControls);
updateChartPeriodControls();

let svcChart;
let currentBars = [];
let currentServices = [];
let chartSelectedIds = new Set();
function fmtMoney(x){ return Number(x||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }

function buildChartUrl(action){
  const url = new URL(location.href);
  url.search = '';
  url.searchParams.set('action', action);
  const period = chartPeriodSelect?.value || 'all';
  url.searchParams.set('chart_period', period);
  const dayStart = document.getElementById('chartDayStart')?.value;
  const dayEnd = document.getElementById('chartDayEnd')?.value;
  const monthStart = document.getElementById('chartMonthStart')?.value;
  const monthStartYear = document.getElementById('chartMonthStartYear')?.value;
  const monthEnd = document.getElementById('chartMonthEnd')?.value;
  const monthEndYear = document.getElementById('chartMonthEndYear')?.value;
  const yearStart = document.getElementById('chartYearStart')?.value;
  const yearEnd = document.getElementById('chartYearEnd')?.value;
  if (dayStart) url.searchParams.set('day_start', dayStart);
  if (dayEnd) url.searchParams.set('day_end', dayEnd);
  if (monthStart) url.searchParams.set('month_start', monthStart);
  if (monthStartYear) url.searchParams.set('month_start_year', monthStartYear);
  if (monthEnd) url.searchParams.set('month_end', monthEnd);
  if (monthEndYear) url.searchParams.set('month_end_year', monthEndYear);
  if (yearStart) url.searchParams.set('year_start', yearStart);
  if (yearEnd) url.searchParams.set('year_end', yearEnd);
  return url;
}

function destroyChart(){ if (svcChart) { svcChart.destroy(); svcChart = null; } }

function updateCheckboxUI(){
  document.querySelectorAll('#chartServiceCheckboxes input[type="checkbox"]').forEach(input => {
    input.checked = chartSelectedIds.has(String(input.value));
  });
}

function buildCheckboxes(){
  const container = document.getElementById('chartServiceCheckboxes');
  container.innerHTML = '';
  currentServices.forEach(svc => {
    const wrap = document.createElement('div');
    wrap.className = 'form-check form-check-inline';
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.className = 'form-check-input';
    input.id = `chart-svc-${svc.service_id}`;
    input.value = String(svc.service_id);
    input.checked = chartSelectedIds.has(String(svc.service_id));
    input.addEventListener('change', () => {
      if (input.checked) chartSelectedIds.add(input.value); else chartSelectedIds.delete(input.value);
      renderBarChart();
    });
    const label = document.createElement('label');
    label.className = 'form-check-label';
    label.setAttribute('for', input.id);
    label.textContent = `${svc.service_name} (${(svc.total_booking||0).toLocaleString()})`;
    wrap.appendChild(input);
    wrap.appendChild(label);
    container.appendChild(wrap);
  });
}

function populateTopList(items){
  const list = document.getElementById('topServiceList');
  list.innerHTML = '';
  if (!items || items.length === 0) {
    const li = document.createElement('li');
    li.className = 'list-group-item text-muted';
    li.textContent = 'ไม่มีข้อมูล';
    list.appendChild(li);
    return;
  }
  items.forEach(item => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    const name = document.createElement('span');
    name.textContent = item.service_name;
    const badge = document.createElement('span');
    badge.className = 'badge bg-primary rounded-pill';
    badge.textContent = (item.total_booking||0).toLocaleString();
    li.appendChild(name);
    li.appendChild(badge);
    list.appendChild(li);
  });
}

function renderBarChart(){
  const titleEl = document.getElementById('chartDetailTitle');
  document.getElementById('chartBackButton')?.classList.add('d-none');
  const selected = chartSelectedIds.size ? chartSelectedIds : new Set(currentServices.map(s=>String(s.service_id)));
  if (!chartSelectedIds.size) {
    currentServices.forEach(s=>selected.add(String(s.service_id)));
    chartSelectedIds = new Set(selected);
    updateCheckboxUI();
  }
  const dataset = currentBars.filter(bar => selected.has(String(bar.service_id)));
  destroyChart();
  const ctx = document.getElementById('svcChart').getContext('2d');
  svcChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: dataset.map(d => d.service_name),
      datasets: [{
        label: 'จำนวนการจอง',
        data: dataset.map(d => d.total_booking),
        backgroundColor: 'rgba(13,110,253,0.6)',
        borderColor: '#0d6efd'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `จำนวนการจอง: ${(ctx.parsed.y||0).toLocaleString()}`
          }
        }
      },
      onClick: (_evt, elements) => {
        if (!elements.length) return;
        const idx = elements[0].index;
        const target = dataset[idx];
        if (target) {
          loadServiceTimeline(target.service_id, target.service_name);
        }
      }
    }
  });
  titleEl.textContent = 'กราฟจำนวนการจองต่อบริการ';
}

async function loadServiceTimeline(serviceId, serviceName){
  const url = buildChartUrl('timeline');
  url.searchParams.set('service_id', serviceId);
  const res = await fetch(url.toString(), { cache: 'no-store' });
  const data = await res.json();
  destroyChart();
  const ctx = document.getElementById('svcChart').getContext('2d');
  svcChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.labels || [],
      datasets: [{
        label: `จำนวนการจอง (${serviceName})`,
        data: (data.data || []),
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.15)',
        tension: 0.25,
        fill: true,
        pointRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { title: { display: true, text: data.axis || '' } },
        y: { beginAtZero: true, ticks: { precision: 0 } }
      },
      plugins: { legend: { display: false } }
    }
  });
  document.getElementById('chartBackButton')?.classList.remove('d-none');
  document.getElementById('chartDetailTitle').textContent = `กราฟบริการ: ${serviceName}`;
}

async function loadChartData(){
  const url = buildChartUrl('stats');
  const res = await fetch(url.toString(), { cache: 'no-store' });
  const data = await res.json();
  if (data.error) { console.error(data.error); return; }

  document.getElementById('k_net_total').textContent = fmtMoney(data.cards.net_total);
  document.getElementById('k_tx_total').textContent = (data.cards.tx_total||0).toLocaleString();
  document.getElementById('k_customers_total').textContent = (data.cards.customers_total||0).toLocaleString();
  document.getElementById('k_services_total').textContent = (data.cards.services_sold||0).toLocaleString();
  document.getElementById('k_top_service').textContent = data.cards.top_service || '-';
  document.getElementById('k_top_service_net').textContent = fmtMoney(data.cards.top_service_net||0);

  currentBars = data.chart?.bars || [];
  currentServices = data.chart?.services || [];
  const prevSelection = new Set(chartSelectedIds);
  chartSelectedIds = new Set();
  currentServices.forEach(s => {
    const id = String(s.service_id);
    if (!prevSelection.size || prevSelection.has(id)) {
      chartSelectedIds.add(id);
    }
  });
  if (!chartSelectedIds.size) {
    currentServices.forEach(s => chartSelectedIds.add(String(s.service_id)));
  }
  buildCheckboxes();
  populateTopList(data.chart?.top10 || []);
  renderBarChart();
}

document.getElementById('applyChart')?.addEventListener('click', (e)=>{
  e.preventDefault();
  loadChartData();
});
document.getElementById('chart-tab')?.addEventListener('shown.bs.tab', ()=> loadChartData());
document.getElementById('chartBackButton')?.addEventListener('click', ()=> renderBarChart());
document.getElementById('chartSelectAll')?.addEventListener('click', ()=>{
  if (chartSelectedIds.size === currentServices.length) {
    chartSelectedIds.clear();
  } else {
    chartSelectedIds = new Set(currentServices.map(s=>String(s.service_id)));
  }
  updateCheckboxUI();
  renderBarChart();
});
</script>
</body>
</html>
