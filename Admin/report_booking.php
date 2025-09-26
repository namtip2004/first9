<?php
// report_booking.php — Booking report with 2 tabs (Table + Chart), KPI cards (global), status-lines chart
require_once("connect_db.php");
function esc($s){return htmlspecialchars($s??'',ENT_QUOTES,'UTF-8');}

// ----------------------- meta: year range for chart pickers -----------------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ----------------------- AJAX: stats for cards + chart -----------------------
if(isset($_GET['action']) && $_GET['action']==='stats'){
  header('Content-Type: application/json; charset=utf-8');

  $view = $_GET['view'] ?? 'years';
  $view = in_array($view, ['years','months','days','day_table'], true) ? $view : 'years';
  $year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
  $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $day   = isset($_GET['day']) ? (int)$_GET['day'] : (int)date('j');

  $staff   = isset($_GET['staff'])   ? (int)$_GET['staff']   : 0;
  $service = isset($_GET['service']) ? (int)$_GET['service'] : 0;

  $k_total = (int)$conn->query("SELECT COUNT(*) c FROM booking")->fetch_assoc()['c'];
  $k_conf  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_CONFIRMED))->fetch_assoc()['c'];
  $k_pend  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_PENDING))->fetch_assoc()['c'];
  $k_canc  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_CANCELLED))->fetch_assoc()['c'];

  $cardsPayload = [
    'total'     => $k_total,
    'confirmed' => $k_conf,
    'pending'   => $k_pend,
    'cancelled' => $k_canc
  ];

  $confirmedStatus = BOOKING_STATUS_CONFIRMED;
  $completedStatus = BOOKING_STATUS_COMPLATE;

  $conditions = ["b.status IN ($confirmedStatus,$completedStatus)"];
  $types = '';
  $params = [];
  if($staff>0){
    $conditions[] = 'b.staff_id=?';
    $types .= 'i';
    $params[] = $staff;
  }
  if($service>0){
    $conditions[] = 'EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=?)';
    $types .= 'i';
    $params[] = $service;
  }
  $where = implode(' AND ', $conditions);

  if($view === 'day_table'){
    if($month < 1 || $month > 12){ $month = (int)date('n'); }
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    if($day < 1 || $day > $daysInMonth){ $day = min($daysInMonth, max(1, (int)date('j'))); }

    $sql = "SELECT
        b.booking_id,
        b.b_created_at,
        b.booking_date,
        b.time_start,
        b.time_end,
        b.total_price,
        b.total_discount,
        b.final_price,
        c.customer_name,
        s.staff_name,
        COALESCE(GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', '), '') AS services
      FROM booking b
      LEFT JOIN customer c ON b.customer_id=c.customer_id
      LEFT JOIN staff s ON b.staff_id=s.staff_id
      LEFT JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      LEFT JOIN service_option so ON bs.option_id=so.option_id
      LEFT JOIN service sv ON so.service_id=sv.service_id
      WHERE $where AND YEAR(b.booking_date)=? AND MONTH(b.booking_date)=? AND DAY(b.booking_date)=?
      GROUP BY b.booking_id
      ORDER BY b.booking_date ASC, b.time_start ASC";

    $stmt = $conn->prepare($sql);
    $bindTypes = $types . 'iii';
    $bindParams = $params;
    $bindParams[] = $year;
    $bindParams[] = $month;
    $bindParams[] = $day;
    if($bindTypes !== ''){
      $stmt->bind_param($bindTypes, ...$bindParams);
    }
    $stmt->execute();
    $rs = $stmt->get_result();

    $rows=[]; $gross=0.0; $disc=0.0; $net=0.0;
    while($row=$rs->fetch_assoc()){
      $bookedAt = $row['b_created_at'] ? date('Y-m-d H:i', strtotime($row['b_created_at'])) : '-';
      $serviceAt = $row['booking_date'] ? $row['booking_date'] : '';
      $start = $row['time_start'] ? substr($row['time_start'],0,5) : '';
      $end = $row['time_end'] ? substr($row['time_end'],0,5) : '';
      $timeRange = '';
      if($start !== '' && $end !== ''){
        $timeRange = $start.'-'.$end;
      }elseif($start !== ''){
        $timeRange = $start;
      }elseif($end !== ''){
        $timeRange = $end;
      }
      if($serviceAt !== ''){
        $serviceAt = trim($serviceAt.' '.trim($timeRange));
      }
      $gross += (float)($row['total_price'] ?? 0);
      $disc  += (float)($row['total_discount'] ?? 0);
      $net   += (float)($row['final_price'] ?? 0);
      $rows[] = [
        'booking_id'   => (int)$row['booking_id'],
        'booked_at'    => $bookedAt,
        'service_time' => $serviceAt,
        'customer'     => $row['customer_name'] ?? '',
        'staff'        => $row['staff_name'] ?? '',
        'services'     => $row['services'] ?? '',
        'gross'        => round((float)($row['total_price'] ?? 0), 2),
        'discount'     => round((float)($row['total_discount'] ?? 0), 2),
        'net'          => round((float)($row['final_price'] ?? 0), 2)
      ];
    }
    $stmt->close();

    $dateIso = sprintf('%04d-%02d-%02d', $year, $month, $day);

    echo json_encode([
      'type'     => 'day_table',
      'date_iso' => $dateIso,
      'rows'     => $rows,
      'totals'   => [
        'gross'    => round($gross, 2),
        'discount' => round($disc, 2),
        'net'      => round($net, 2)
      ],
      'cards' => $cardsPayload
    ]);
    exit;
  }

  if($view === 'months'){
    if($year < $minYear || $year > $maxYear){ $year = (int)date('Y'); }
    $sql = "SELECT MONTH(b.booking_date) AS m, COALESCE(SUM(b.final_price),0) AS net FROM booking b WHERE $where AND YEAR(b.booking_date)=? GROUP BY MONTH(b.booking_date) ORDER BY MONTH(b.booking_date)";
    $stmt = $conn->prepare($sql);
    $bindTypes = $types . 'i';
    $bindParams = $params;
    $bindParams[] = $year;
    if($bindTypes !== ''){
      $stmt->bind_param($bindTypes, ...$bindParams);
    }
    $stmt->execute();
    $rs = $stmt->get_result();
    $map=[];
    while($row=$rs->fetch_assoc()){
      $map[(int)$row['m']] = round((float)($row['net'] ?? 0),2);
    }
    $stmt->close();
    $labels=[]; $keys=[]; $values=[];
    for($m=1;$m<=12;$m++){
      $keys[] = $m;
      $labels[] = str_pad((string)$m,2,'0',STR_PAD_LEFT);
      $values[] = $map[$m] ?? 0;
    }
    echo json_encode([
      'type'     => 'months',
      'year'     => $year,
      'labels'   => $labels,
      'keys'     => $keys,
      'series'   => ['net'=>$values],
      'axis'     => 'Months',
      'title'    => "Monthly revenue (Year $year)",
      'subtitle' => 'Click a month bar to view daily revenue',
      'cards'    => $cardsPayload
    ]);
    exit;
  }

  if($view === 'days'){
    if($month < 1 || $month > 12){ $month = (int)date('n'); }
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $sql = "SELECT DAY(b.booking_date) AS d, COALESCE(SUM(b.final_price),0) AS net FROM booking b WHERE $where AND YEAR(b.booking_date)=? AND MONTH(b.booking_date)=? GROUP BY DAY(b.booking_date) ORDER BY DAY(b.booking_date)";
    $stmt = $conn->prepare($sql);
    $bindTypes = $types . 'ii';
    $bindParams = $params;
    $bindParams[] = $year;
    $bindParams[] = $month;
    if($bindTypes !== ''){
      $stmt->bind_param($bindTypes, ...$bindParams);
    }
    $stmt->execute();
    $rs = $stmt->get_result();
    $map=[];
    while($row=$rs->fetch_assoc()){
      $map[(int)$row['d']] = round((float)($row['net'] ?? 0),2);
    }
    $stmt->close();
    $labels=[]; $keys=[]; $values=[];
    for($d=1;$d<=$daysInMonth;$d++){
      $keys[] = $d;
      $labels[] = str_pad((string)$d,2,'0',STR_PAD_LEFT);
      $values[] = $map[$d] ?? 0;
    }
    echo json_encode([
      'type'     => 'days',
      'year'     => $year,
      'month'    => $month,
      'labels'   => $labels,
      'keys'     => $keys,
      'series'   => ['net'=>$values],
      'axis'     => 'Days',
      'title'    => "Daily revenue (Year $year Month $month)",
      'subtitle' => 'Click a day bar to view booking details',
      'cards'    => $cardsPayload
    ]);
    exit;
  }

  $sql = "SELECT YEAR(b.booking_date) AS y, COALESCE(SUM(b.final_price),0) AS net FROM booking b WHERE $where GROUP BY YEAR(b.booking_date) ORDER BY YEAR(b.booking_date)";
  $stmt = $conn->prepare($sql);
  if($types !== ''){
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $rs = $stmt->get_result();
  $labels=[]; $keys=[]; $values=[];
  while($row=$rs->fetch_assoc()){
    $y = (int)$row['y'];
    if($y===0){ continue; }
    $keys[] = $y;
    $labels[] = (string)$y;
    $values[] = round((float)($row['net'] ?? 0),2);
  }
  $stmt->close();

  if(empty($labels)){
    $yearNow = (int)date('Y');
    $keys[] = $yearNow;
    $labels[] = (string)$yearNow;
    $values[] = 0;
  }

  echo json_encode([
    'type'     => 'years',
    'labels'   => $labels,
    'keys'     => $keys,
    'series'   => ['net'=>$values],
    'axis'     => 'Years',
    'title'    => 'Annual revenue',
    'subtitle' => 'Click a year bar to view monthly revenue',
    'cards'    => $cardsPayload
  ]);
  exit;
}
// ----------------------- TABLE: filters + sort + pagination -----------------------
$searchId = trim($_GET['booking_id'] ?? '');
$statusParam = $_GET['status'] ?? 'all';
$statusCode = $statusParam === 'all' ? null : booking_status_code($statusParam);
$statusValue = $statusCode === null ? 'all' : (string)$statusCode;
$staffF = isset($_GET['staff']) ? (int)$_GET['staff'] : 0;
$serviceF = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$customerF = isset($_GET['customer']) ? (int)$_GET['customer'] : 0;
$period = $_GET['period'] ?? 'all';
$period = in_array($period, ['all','date','month','year'], true) ? $period : 'all';
$periodDate = $_GET['period_date'] ?? '';
$periodMonth = isset($_GET['period_month']) ? (int)$_GET['period_month'] : (int)date('n');
$periodMonthYear = isset($_GET['period_month_year']) ? (int)$_GET['period_month_year'] : (int)date('Y');
$periodYear = isset($_GET['period_year']) ? (int)$_GET['period_year'] : (int)date('Y');
$periodMonth = ($periodMonth >=1 && $periodMonth <=12) ? $periodMonth : (int)date('n');
if($periodMonthYear < $minYear) { $periodMonthYear = $minYear; }
elseif($periodMonthYear > $maxYear) { $periodMonthYear = $maxYear; }
if($periodYear < $minYear) { $periodYear = $minYear; }
elseif($periodYear > $maxYear) { $periodYear = $maxYear; }
$sort   = $_GET['sort'] ?? 'booked_at';
$sort = in_array($sort, ['booked_at','service_time'], true) ? $sort : 'booked_at';
$dir    = strtoupper($_GET['dir'] ?? 'DESC'); $dir=$dir==='ASC'?'ASC':'DESC';

$sortMap=[
  'booked_at'   => "COALESCE(b.b_created_at, CONCAT(b.booking_date, ' ', b.time_start)) $dir",
  'service_time'=> "b.booking_date $dir, b.time_start $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['booked_at'];

// WHERE conditions (base)
$where = ["1=1"]; $types=""; $params=[];
if($searchId!==''){
  if(ctype_digit($searchId)){
    $where[]="b.booking_id = ?";
    $params[]=(int)$searchId; $types.='i';
  }else{
    $where[]="CAST(b.booking_id AS CHAR) LIKE ?";
    $params[]='%'.$searchId.'%'; $types.='s';
  }
}
if($statusCode !== null){
  $where[]="b.status=?"; $params[]=$statusCode; $types.="i";
}
if($staffF>0){
  $where[]="b.staff_id=?"; $params[]=$staffF; $types.="i";
}
if($customerF>0){
  $where[]="b.customer_id=?"; $params[]=$customerF; $types.='i';
}
// Service filter via EXISTS (avoid row dup)
if($serviceF>0){
  $where[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=?)";
  $params[]=$serviceF; $types.="i";
}
switch($period){
  case 'date':
    if($periodDate!==''){
      $where[]="b.booking_date=?";
      $params[]=$periodDate; $types.='s';
    }
    break;
  case 'month':
    $where[]="YEAR(b.booking_date)=?"; $params[]=$periodMonthYear; $types.='i';
    $where[]="MONTH(b.booking_date)=?"; $params[]=$periodMonth; $types.='i';
    break;
  case 'year':
    $where[]="YEAR(b.booking_date)=?"; $params[]=$periodYear; $types.='i';
    break;
}
$whereSql = implode(" AND ",$where);

// Count distinct bookings
$sqlCount="SELECT COUNT(*) cnt
           FROM booking b
           LEFT JOIN customer c ON c.customer_id=b.customer_id
           LEFT JOIN staff s ON s.staff_id=b.staff_id
           WHERE $whereSql";
$stmt=$conn->prepare($sqlCount);
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute(); $total=(int)$stmt->get_result()->fetch_assoc()['cnt']; $stmt->close();

$per = 20;
$pages = max(1, (int)ceil($total / $per));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$off = ($page-1)*$per;

// List with services (GROUP_CONCAT)
$sqlList="
  SELECT
    b.booking_id, b.booking_date, b.time_start, b.time_end, b.total_price, b.total_discount, b.final_price, b.status, b.b_created_at,
    c.customer_name, c.gmail, s.staff_name,
    GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', ') AS services
  FROM booking b
  LEFT JOIN customer c ON b.customer_id=c.customer_id
  LEFT JOIN staff s ON b.staff_id=s.staff_id
  LEFT JOIN booking_seviceop bs ON b.booking_id=bs.booking_id
  LEFT JOIN service_option so ON bs.option_id=so.option_id
  LEFT JOIN service sv ON so.service_id=sv.service_id
  WHERE $whereSql
  GROUP BY b.booking_id
  ORDER BY $orderBy
  LIMIT ? OFFSET ?
";
$params2=$params; $types2=$types."ii"; $params2[]=$per; $params2[]=$off;
$stmt=$conn->prepare($sqlList); $stmt->bind_param($types2,...$params2);
$stmt->execute(); $rs=$stmt->get_result();
$rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r; $stmt->close();

// dropdown options
$staffOps = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name");
$serviceOps = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");
$customerOps = $conn->query("SELECT customer_id, customer_name FROM customer ORDER BY customer_name");

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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Booking Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    .small-label{font-size:.9rem;color:#6c757d}
    .kpi-value{font-size:28px;font-weight:700;margin:0}
    .card-icon{font-size:28px}
  </style>
</head>
<body>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>
<main id="main" class="main">

  <div class="pagetitle"><h1>Booking Report</h1></div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-table" type="button" role="tab">Table</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-chart" type="button" role="tab" id="chart-tab">Chart</button></li>
  </ul>

  <div class="tab-content">
    <!-- TAB: TABLE -->
    <div class="tab-pane fade show active" id="t-table" role="tabpanel">
      <div class="card mt-3"><div class="card-body">

        <form class="row g-2 align-items-end" method="get">
          <input type="hidden" name="tab" value="table">
          <div class="col-md-auto">
            <label class="form-label small-label">Search by ID</label>
            <input type="text" class="form-control" name="booking_id" value="<?=esc($searchId)?>" placeholder="e.g. 1001">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Status</label>
            <select class="form-select" name="status">
              <?php
                $statusOptions = ['all' => 'All'];
                foreach (booking_status_options() as $code => $label) {
                  $statusOptions[(string)$code] = $label;
                }
                foreach ($statusOptions as $key => $label):
              ?>
                <option value="<?=$key?>" <?= $statusValue === $key ? 'selected' : '' ?>><?=$label?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Service</label>
            <select class="form-select" name="service">
              <option value="0">All</option>
              <?php while($sv=$serviceOps->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>" <?= $serviceF==$sv['service_id']?'selected':'' ?>><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Staff</label>
            <select class="form-select select-search" name="staff" data-width="style" data-placeholder="Select staff" style="width:220px;">
              <option value="0">All</option>
              <?php while($s=$staffOps->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>" <?= $staffF==$s['staff_id']?'selected':'' ?>><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Customer</label>
            <select class="form-select select-search" name="customer" data-width="style" data-placeholder="Select customer" style="width:220px;">
              <option value="0">All</option>
              <?php while($cus=$customerOps->fetch_assoc()): ?>
                <option value="<?=$cus['customer_id']?>" <?= $customerF==$cus['customer_id']?'selected':'' ?>><?=esc($cus['customer_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Period</label>
            <select class="form-select" name="period" id="periodSelect">
              <option value="all" <?= $period==='all'?'selected':'' ?>>All</option>
              <option value="date" <?= $period==='date'?'selected':'' ?>>By day</option>
              <option value="month" <?= $period==='month'?'selected':'' ?>>By month</option>
              <option value="year" <?= $period==='year'?'selected':'' ?>>By year</option>
            </select>
          </div>
          <div class="col-md-auto period-control <?= $period==='date'?'':'d-none' ?>" id="period-date">
            <label class="form-label small-label">Select date</label>
            <input type="date" class="form-control" name="period_date" value="<?=esc($periodDate)?>">
          </div>
          <div class="col-md-auto period-control <?= $period==='month'?'':'d-none' ?>" id="period-month">
            <label class="form-label small-label">Month / Year</label>
            <div class="d-flex gap-2">
              <select class="form-select" name="period_month">
                <?php for($m=1;$m<=12;$m++): ?>
                  <option value="<?=$m?>" <?= $periodMonth==$m?'selected':'' ?>><?=$m?></option>
                <?php endfor; ?>
              </select>
              <select class="form-select" name="period_month_year">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $periodMonthYear==$y?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto period-control <?= $period==='year'?'':'d-none' ?>" id="period-year">
            <label class="form-label small-label">Select year</label>
            <select class="form-select" name="period_year">
              <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                <option value="<?=$y?>" <?= $periodYear==$y?'selected':'' ?>><?=$y?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Sort by</label>
            <div class="input-group">
              <select class="form-select" name="sort">
                <option value="booked_at" <?= $sort==='booked_at'?'selected':'' ?>>Booking created</option>
                <option value="service_time" <?= $sort==='service_time'?'selected':'' ?>>Service time</option>
              </select>
              <select class="form-select" name="dir">
                <option value="ASC"  <?= $dir==='ASC'?'selected':'' ?>>Oldest → newest</option>
                <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>Newest → oldest</option>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Apply filters</button>
          </div>
          <div class="ms-auto col-md-auto">
            <span class="badge bg-primary">Total: <?=number_format($total)?> records</span>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle" id="bookingTable">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Booking created</th>
                <th>Service time</th>
                <th>Customer</th>
                <th>Services</th>
                <th>Staff</th>
                <th class="text-end">Gross amount</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Net amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="10" class="text-center text-muted">No data found</td></tr>
              <?php else: foreach($rows as $bk): ?>
                <?php
                  $bookedAt = $bk['b_created_at'] ? date('Y-m-d H:i', strtotime($bk['b_created_at'])) : '-';
                  $serviceDate = $bk['booking_date'] ? date('Y-m-d', strtotime($bk['booking_date'])) : '-';
                  $serviceStart = $bk['time_start'] ? substr($bk['time_start'],0,5) : '';
                  $serviceEnd = $bk['time_end'] ? substr($bk['time_end'],0,5) : '';
                  if($serviceStart && $serviceEnd){
                    $serviceRange = $serviceStart.' - '.$serviceEnd;
                  }elseif($serviceStart){
                    $serviceRange = $serviceStart;
                  }elseif($serviceEnd){
                    $serviceRange = $serviceEnd;
                  }else{
                    $serviceRange = '';
                  }
                  $serviceDateTime = trim($serviceDate . ' ' . $serviceRange);
                ?>
                <tr>
                  <td><?=esc($bk['booking_id'])?></td>
                  <td><?=esc($bookedAt)?></td>
                  <td><?=esc($serviceDateTime !== '' ? $serviceDateTime : '-')?></td>
                  <td>
                    <?=esc($bk['customer_name']?:'N/A')?><br>
                    <small class="text-muted"><?=esc($bk['gmail']?:'-')?></small>
                  </td>
                  <td><?=esc($bk['services']?:'N/A')?></td>
                  <td><?=esc($bk['staff_name']?:'N/A')?></td>
                  <td class="text-end">฿<?=number_format((float)($bk['total_price'] ?? 0),2)?></td>
                  <td class="text-end text-danger">฿<?=number_format((float)($bk['total_discount'] ?? 0),2)?></td>
                  <td class="text-end text-success fw-bold">฿<?=number_format((float)$bk['final_price'],2)?></td>
                  <td>
                    <?php $stCode = booking_status_code($bk['status']); $badgeClass = booking_status_badge_class($stCode); $label = booking_status_label($stCode); ?>
                    <span class="badge <?=$badgeClass?>"><?=esc($label ?: 'N/A')?></span>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <?php if($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="text-muted">Page <?=number_format($page)?> / <?=number_format($pages)?></span>
          <div class="btn-group">
            <?php if($page > 1): ?>
              <a class="btn btn-outline-secondary" href="<?=esc($pageUrl($page-1))?>"><i class="bi bi-chevron-left"></i> Previous</a>
            <?php else: ?>
              <span class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-left"></i> Previous</span>
            <?php endif; ?>
            <?php if($page < $pages): ?>
              <a class="btn btn-outline-primary" href="<?=esc($pageUrl($page+1))?>">Next <i class="bi bi-chevron-right"></i></a>
            <?php else: ?>
              <span class="btn btn-outline-primary disabled">Next <i class="bi bi-chevron-right"></i></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

      </div></div>
    </div>

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="t-chart" role="tabpanel">
      <div class="card mt-3"><div class="card-body">

        <!-- Chart filters -->
        <div class="row g-3 align-items-end">
          <div class="col-md-auto">
            <label class="form-label small-label">Staff</label>
            <select id="chart_staff" class="form-select">
              <option value="0">All</option>
              <?php $staffOps2 = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name"); while($s=$staffOps2->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>"><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Service</label>
            <select id="chart_service" class="form-select">
              <option value="0">All</option>
              <?php $serviceOps2 = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name"); while($sv=$serviceOps2->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>"><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <button id="applyChart" class="btn btn-primary mt-4 mt-md-0"><i class="bi bi-funnel"></i> Apply filters</button>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mt-3 gap-3">
          <div>
            <h5 class="mb-1" id="chartTitle">Annual revenue</h5>
            <div class="text-muted" id="chartSubtitle">Click a bar to drill down to the next level</div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-secondary-subtle text-dark" id="chartLevel">Level: All years</span>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="backToYears"><i class="bi bi-arrow-counterclockwise"></i> Back to year level</button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="backToMonths"><i class="bi bi-arrow-left-short"></i> Back to month level</button>
          </div>
        </div>

        <!-- KPI Cards (GLOBAL; not filtered) -->
        <div class="row mt-3">
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-calendar3 card-icon text-primary"></i>
            <div><div class="kpi-value" id="k_total">0</div><div class="text-muted">Total</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-check2-circle card-icon text-success"></i>
            <div><div class="kpi-value" id="k_conf">0</div><div class="text-muted">Confirmed</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-hourglass-split card-icon text-warning"></i>
            <div><div class="kpi-value" id="k_pend">0</div><div class="text-muted">Pending</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-x-octagon card-icon text-danger"></i>
            <div><div class="kpi-value" id="k_canc">0</div><div class="text-muted">Cancelled</div></div>
          </div></div></div>
        </div>
        <div class="text-muted">* Cards ignore the filters above</div>

        <!-- CHART -->
        <div class="mt-3" style="min-height:360px">
          <canvas id="bkChart" height="120"></canvas>
        </div>
        <div class="text-muted mt-2">* Click a bar to view the next level, or use the back buttons to return</div>

        <div class="mt-4 d-none" id="dayTableWrap">
          <h5 id="dayTableHeading" class="mb-3"></h5>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Booked at</th>
                  <th>Service date/time</th>
                  <th>Customer</th>
                  <th>Staff</th>
                  <th>Services</th>
                  <th class="text-end">Gross</th>
                  <th class="text-end">Discount</th>
                  <th class="text-end">Net</th>
                </tr>
              </thead>
              <tbody id="dayTableBody"></tbody>
            </table>
          </div>
          <div class="mt-3 text-end">
            <div>Total before discount: <strong id="dayTotalsGross">฿0.00</strong></div>
            <div>Total discount: <strong id="dayTotalsDiscount">฿0.00</strong></div>
            <div>Total net: <strong id="dayTotalsNet">฿0.00</strong></div>
          </div>
        </div>

      </div></div>
    </div>
  </div>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const periodSelect=document.getElementById('periodSelect');
function updatePeriodFilters(){
  if(!periodSelect) return;
  const v=periodSelect.value;
  document.getElementById('period-date')?.classList.toggle('d-none',v!=='date');
  document.getElementById('period-month')?.classList.toggle('d-none',v!=='month');
  document.getElementById('period-year')?.classList.toggle('d-none',v!=='year');
}
periodSelect?.addEventListener('change',updatePeriodFilters);
updatePeriodFilters();

const currencyFmt = new Intl.NumberFormat('en-US',{style:'currency',currency:'THB',maximumFractionDigits:2});
const numberFmt = new Intl.NumberFormat('en-US');

function formatCurrency(value){
  const num = typeof value === 'number' ? value : Number(value || 0);
  return currencyFmt.format(Number.isFinite(num) ? num : 0);
}

function formatDateDisplay(iso){
  if(!iso) return '';
  const dt = new Date(iso + 'T00:00:00');
  if(Number.isNaN(dt.getTime())) return iso;
  return dt.toLocaleDateString('en-US',{dateStyle:'long'});
}

let chart;
let chartView='years';
let chartKeys=[];
let selectedYear=null;
let selectedMonth=null;
let latestChartRequestId=0;
let latestDayRequestId=0;

function updateCards(cards){
  if(!cards) return;
  document.getElementById('k_total').textContent = numberFmt.format(cards.total ?? 0);
  document.getElementById('k_conf').textContent = numberFmt.format(cards.confirmed ?? 0);
  document.getElementById('k_pend').textContent = numberFmt.format(cards.pending ?? 0);
  document.getElementById('k_canc').textContent = numberFmt.format(cards.cancelled ?? 0);
}

async function requestData(view, extra={}){
  const params = new URLSearchParams();
  params.set('action','stats');
  params.set('view',view);
  const staffEl = document.getElementById('chart_staff');
  const serviceEl = document.getElementById('chart_service');
  if(staffEl) params.set('staff', staffEl.value || '0');
  if(serviceEl) params.set('service', serviceEl.value || '0');
  if(extra.year !== undefined && extra.year !== null) params.set('year', extra.year);
  if(extra.month !== undefined && extra.month !== null) params.set('month', extra.month);
  if(extra.day !== undefined && extra.day !== null) params.set('day', extra.day);
  const res = await fetch('report_booking.php?' + params.toString(), {cache:'no-store'});
  if(!res.ok) throw new Error('Unable to fetch data');
  return res.json();
}

function updateBreadcrumb(){
  const levelEl = document.getElementById('chartLevel');
  const backYears = document.getElementById('backToYears');
  const backMonths = document.getElementById('backToMonths');
  if(chartView==='years'){
    levelEl.textContent = 'Level: All years';
    backYears?.classList.add('d-none');
    backMonths?.classList.add('d-none');
  }else if(chartView==='months'){
    const yearText = selectedYear ? selectedYear.toString() : '-';
    levelEl.textContent = `Level: Year ${yearText}`;
    backYears?.classList.remove('d-none');
    backMonths?.classList.add('d-none');
  }else if(chartView==='days'){
    const yearText = selectedYear ? selectedYear.toString() : '-';
    const monthText = selectedMonth ? String(selectedMonth).padStart(2,'0') : '-';
    levelEl.textContent = `Level: Year ${yearText} / Month ${monthText}`;
    backYears?.classList.remove('d-none');
    backMonths?.classList.remove('d-none');
  }
}

function renderChart(data){
  const labels = data.labels ?? [];
  const values = data.series?.net ?? [];
  document.getElementById('chartTitle').textContent = data.title || 'Revenue';
  document.getElementById('chartSubtitle').textContent = data.subtitle || '';

  if(chart) chart.destroy();
  const ctx = document.getElementById('bkChart').getContext('2d');
  chart = new Chart(ctx,{
    type:'bar',
    data:{
      labels,
      datasets:[{
        label:'Revenue (THB)',
        data:values,
        backgroundColor:'#0d6efd',
        hoverBackgroundColor:'#0b5ed7',
        borderRadius:6,
        maxBarThickness:48
      }]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        x:{ title:{display:true,text:data.axis||''} },
        y:{ beginAtZero:true, ticks:{ callback:value=>formatCurrency(value) } }
      },
      plugins:{
        legend:{display:false},
        tooltip:{ callbacks:{ label:ctx=>`Revenue: ${formatCurrency(ctx.parsed.y)}` } }
      },
      onClick:(evt,elements)=>{
        if(!elements?.length) return;
        const idx = elements[0].index;
        const key = chartKeys[idx];
        if(chartView==='years'){
          selectedYear = Number(key);
          selectedMonth = null;
          if(Number.isFinite(selectedYear)){
            loadChart('months',{year:selectedYear});
          }
        }else if(chartView==='months'){
          selectedMonth = Number(key);
          if(Number.isFinite(selectedMonth)){
            loadChart('days',{year:selectedYear,month:selectedMonth});
          }
        }else if(chartView==='days'){
          const day = Number(key);
          if(Number.isFinite(day)){
            showDayTable(day);
          }
        }
      }
    }
  });
  updateBreadcrumb();
}

function renderDayTable(data){
  const wrap = document.getElementById('dayTableWrap');
  const body = document.getElementById('dayTableBody');
  if(!wrap || !body){ return; }
  if(!data || data.type !== 'day_table'){
    wrap.classList.add('d-none');
    body.innerHTML='';
    return;
  }
  wrap.classList.remove('d-none');
  body.innerHTML='';
  const rows = Array.isArray(data.rows) ? data.rows : [];
  if(rows.length===0){
    const tr=document.createElement('tr');
    const td=document.createElement('td');
    td.colSpan=9;
    td.className='text-center text-muted';
    td.textContent='No data for the selected day';
    tr.appendChild(td);
    body.appendChild(tr);
  }else{
    rows.forEach(row=>{
      const tr=document.createElement('tr');
      const cells=[
        row.booking_id ?? '',
        row.booked_at ?? '',
        row.service_time ?? '',
        row.customer || '-',
        row.staff || '-',
        row.services || '-',
        formatCurrency(row.gross ?? 0),
        formatCurrency(row.discount ?? 0),
        formatCurrency(row.net ?? 0)
      ];
      cells.forEach((value,idx)=>{
        const td=document.createElement('td');
        if(idx>=6) td.classList.add('text-end');
        td.textContent=value;
        tr.appendChild(td);
      });
      body.appendChild(tr);
    });
  }
  const heading=document.getElementById('dayTableHeading');
  if(heading){
    const display=formatDateDisplay(data.date_iso);
    heading.textContent=display ? `Revenue details for ${display}` : 'Daily revenue details';
  }
  document.getElementById('dayTotalsGross').textContent = formatCurrency(data.totals?.gross ?? 0);
  document.getElementById('dayTotalsDiscount').textContent = formatCurrency(data.totals?.discount ?? 0);
  document.getElementById('dayTotalsNet').textContent = formatCurrency(data.totals?.net ?? 0);
}

async function loadChart(view='years', extra={}){
  const requestId = ++latestChartRequestId;
  try{
    const data = await requestData(view, extra);
    if(requestId !== latestChartRequestId) return;
    updateCards(data.cards);
    if(data.type==='years'){
      selectedYear=null;
      selectedMonth=null;
    }else{
      if('year' in data) selectedYear = data.year;
      if('month' in data) selectedMonth = data.month;
    }
    if(data.type==='day_table'){
      renderDayTable(data);
      return;
    }
    chartView = data.type;
    chartKeys = data.keys || [];
    renderChart(data);
    renderDayTable(null);
  }catch(err){
    console.error(err);
  }
}

async function showDayTable(day){
  if(!selectedYear || !selectedMonth) return;
  const requestId = ++latestDayRequestId;
  try{
    const data = await requestData('day_table',{year:selectedYear,month:selectedMonth,day});
    if(requestId !== latestDayRequestId) return;
    updateCards(data.cards);
    renderDayTable(data);
  }catch(err){
    console.error(err);
  }
}

function reloadChartForFilters(){
  if(chartView==='months' && selectedYear){
    loadChart('months',{year:selectedYear});
  }else if(chartView==='days' && selectedYear && selectedMonth){
    loadChart('days',{year:selectedYear,month:selectedMonth});
  }else{
    selectedYear=null;
    selectedMonth=null;
    loadChart('years');
  }
}

document.getElementById('applyChart')?.addEventListener('click', reloadChartForFilters);
document.getElementById('backToYears')?.addEventListener('click', ()=>{ selectedYear=null; selectedMonth=null; loadChart('years'); });
document.getElementById('backToMonths')?.addEventListener('click', ()=>{ if(selectedYear){ selectedMonth=null; loadChart('months',{year:selectedYear}); } });

let chartLoaded=false;
const chartTab=document.getElementById('chart-tab');
chartTab?.addEventListener('shown.bs.tab',()=>{
  if(!chartLoaded){
    chartLoaded=true;
    loadChart('years');
  }
});
if(document.getElementById('t-chart')?.classList.contains('show')){
  chartLoaded=true;
  loadChart('years');
}

$(function(){
  $('.select-search').each(function(){
    const $el=$(this);
    const widthAttr=$el.data('width')||'resolve';
    const placeholder=$el.data('placeholder')||'';
    $el.select2({
      theme:'bootstrap-5',
      width:widthAttr,
      placeholder:placeholder,
      language:{
        noResults:()=> 'No results found'
      }
    });
  });
});
</script>
</body>
</html>
