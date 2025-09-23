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

// ==================== AJAX: KPIs + Charts ====================
if (isset($_GET['action']) && $_GET['action']==='stats') {
  header('Content-Type: application/json; charset=utf-8');

  $period = $_GET['period'] ?? 'month';    // all | month | year
  $year   = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
  $month  = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $startY = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $minYear;
  $endY   = isset($_GET['end_year'])   ? (int)$_GET['end_year']   : $maxYear;
  if ($startY > $endY) { $t=$startY; $startY=$endY; $endY=$t; }

  $staffId   = isset($_GET['staff'])   ? (int)$_GET['staff']   : 0;
  $serviceId = isset($_GET['service']) ? (int)$_GET['service'] : 0;

  // ---------- KPI (GLOBAL; all time) ----------
  $total_staff = (int)$conn->query("SELECT COUNT(*) c FROM staff")->fetch_assoc()['c'];
  $confirmed_rev = (float)$conn->query("SELECT COALESCE(SUM(final_price),0) s FROM booking WHERE status={$confirmedStatus}")->fetch_assoc()['s'];
  $confirmed_tx  = (int)$conn->query("SELECT COUNT(*) c FROM booking WHERE status={$confirmedStatus}")->fetch_assoc()['c'];
  $avg_rev_per_staff = $total_staff>0 ? $confirmed_rev / $total_staff : 0.0;

  // Top performer overall
  $tp = $conn->query("
    SELECT s.staff_name, COALESCE(SUM(b.final_price),0) net
    FROM staff s
    LEFT JOIN booking b ON b.staff_id=s.staff_id AND b.status={$confirmedStatus}
    GROUP BY s.staff_id
    ORDER BY net DESC
    LIMIT 1
  ")->fetch_assoc();
  $top_name = $tp['staff_name'] ?? null;
  $top_net  = isset($tp['net']) ? (float)$tp['net'] : 0.0;

  // ---------- Build time buckets ----------
  $labels=[]; $groupExpr=""; $rangeWhere="1=1"; $axis='';
  $rangeStartDate=""; $rangeEndDate="";
  if ($period==='month') {
    $days=cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for($d=1;$d<=$days;$d++) $labels[]=str_pad((string)$d,2,'0',STR_PAD_LEFT);
    $groupExpr="DATE_FORMAT(b.booking_date,'%d')";
    $rangeWhere="YEAR(b.booking_date)=$year AND MONTH(b.booking_date)=$month";
    $axis='วัน'; $rangeStartDate=sprintf('%04d-%02d-01',$year,$month); $rangeEndDate=sprintf('%04d-%02d-%02d',$year,$month,$days);
  } elseif ($period==='year') {
    $labels=['01','02','03','04','05','06','07','08','09','10','11','12'];
    $groupExpr="DATE_FORMAT(b.booking_date,'%m')";
    $rangeWhere="YEAR(b.booking_date)=$year";
    $axis='เดือน'; $rangeStartDate=sprintf('%04d-01-01',$year); $rangeEndDate=sprintf('%04d-12-31',$year);
  } else { // all
    for($y=$startY;$y<=$endY;$y++) $labels[]=(string)$y;
    $groupExpr="YEAR(b.booking_date)";
    $rangeWhere="YEAR(b.booking_date) BETWEEN $startY AND $endY";
    $axis='ปี'; $rangeStartDate=sprintf('%04d-01-01',$startY); $rangeEndDate=sprintf('%04d-12-31',$endY);
  }

  // Optional filters (chart scope)
  $w=[]; $w[]=$rangeWhere;
  if ($staffId>0)   $w[]="b.staff_id=".$staffId;
  if ($serviceId>0) $w[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=".$serviceId.")";
  $where = implode(" AND ", $w);

  // ---------- Time series: bookings count per status + net revenue ----------
  $sql="
    SELECT $groupExpr AS bucket,
           SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS confirmed,
           SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS pending,
           SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS cancelled,
           COUNT(*) AS total,
           COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) AS net_rev
    FROM booking b
    WHERE $where
    GROUP BY bucket
    ORDER BY bucket
  ";
  $rs=$conn->query($sql);
  $mapT=[]; $mapC=[]; $mapP=[]; $mapX=[]; $mapR=[];
  while($row=$rs->fetch_assoc()){
    $k=(string)$row['bucket'];
    $mapT[$k]=(int)$row['total'];
    $mapC[$k]=(int)$row['confirmed'];
    $mapP[$k]=(int)$row['pending'];
    $mapX[$k]=(int)$row['cancelled'];
    $mapR[$k]=(float)$row['net_rev'];
  }
  $sTotal=[]; $sConfirmed=[]; $sPending=[]; $sCancelled=[]; $sNet=[];
  foreach($labels as $b){
    $sTotal[]=$mapT[$b]??0;
    $sConfirmed[]=$mapC[$b]??0;
    $sPending[]=$mapP[$b]??0;
    $sCancelled[]=$mapX[$b]??0;
    $sNet[]=round($mapR[$b]??0,2);
  }

  // ---------- Leaderboards (selected range): Top Staff by Net / by Bookings ----------
  $ws = "b.booking_date BETWEEN '$rangeStartDate' AND '$rangeEndDate'";
  if ($serviceId>0) $ws .= " AND EXISTS (SELECT 1 FROM booking_seviceop bs2 JOIN service_option so2 ON bs2.option_id=so2.option_id WHERE bs2.booking_id=b.booking_id AND so2.service_id=".$serviceId.")";

  $sqlTopNet="
    SELECT s.staff_name, COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) AS net,
           SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS tx_conf
    FROM staff s
    LEFT JOIN booking b ON b.staff_id=s.staff_id AND $ws
    GROUP BY s.staff_id
    ORDER BY net DESC
    LIMIT 10
  ";
  $topNet=[]; $res=$conn->query($sqlTopNet);
  while($row=$res->fetch_assoc()){ $topNet[]=['name'=>$row['staff_name']?:'N/A','net'=>(float)$row['net'],'tx'=>(int)$row['tx_conf']]; }

  $sqlTopTx="
    SELECT s.staff_name,
           SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS tx_conf,
           COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) AS net
    FROM staff s
    LEFT JOIN booking b ON b.staff_id=s.staff_id AND $ws
    GROUP BY s.staff_id
    ORDER BY tx_conf DESC, net DESC
    LIMIT 10
  ";
  $topTx=[]; $res=$conn->query($sqlTopTx);
  while($row=$res->fetch_assoc()){ $topTx[]=['name'=>$row['staff_name']?:'N/A','tx'=>(int)$row['tx_conf'],'net'=>(float)$row['net']]; }

  // ---------- Status mix for a specific staff (or overall) ----------
  $wm = $ws;
  if ($staffId>0) $wm .= " AND b.staff_id=".$staffId;
  $mix = $conn->query("
    SELECT
      SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS confirmed,
      SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS pending,
      SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS cancelled
    FROM booking b
    WHERE $wm
  ")->fetch_assoc();
  $mix = [
    'confirmed'=>(int)($mix['confirmed']??0),
    'pending'=>(int)($mix['pending']??0),
    'cancelled'=>(int)($mix['cancelled']??0)
  ];

  echo json_encode([
    'cards'=>[
      'total_staff'=>$total_staff,
      'confirmed_rev'=>$confirmed_rev,
      'confirmed_tx'=>$confirmed_tx,
      'avg_rev_per_staff'=>$avg_rev_per_staff,
      'top'=>['name'=>$top_name,'net'=>$top_net]
    ],
    'chart'=>[
      'labels'=>$labels,'axis'=>$axis,
      'series'=>[
        'total'=>$sTotal,'confirmed'=>$sConfirmed,'pending'=>$sPending,'cancelled'=>$sCancelled,'net'=>$sNet
      ]
    ],
    'top'=>[ 'by_net'=>$topNet, 'by_tx'=>$topTx ],
    'status_mix'=>$mix
  ]);
  exit;
}

// ==================== TABLE: summary by staff ====================
$search    = trim($_GET['q'] ?? '');
$serviceF  = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';
$sort      = $_GET['sort'] ?? 'net';
$dir       = strtoupper($_GET['dir'] ?? 'DESC'); $dir=$dir==='ASC'?'ASC':'DESC';

$sortMap=[
  'name'   => "s.staff_name $dir",
  'net'    => "net_rev $dir",
  'tx'     => "tx_total $dir",
  'conf'   => "tx_conf $dir",
  'comp'   => "tx_comp $dir",
  'pend'   => "tx_pend $dir",
  'canc'   => "tx_canc $dir",
  'aov'    => "aov $dir",
  'cust'   => "custs $dir",
  'hrs'    => "hrs $dir",
  'avgdur' => "avgdur $dir",
  'first'  => "first_bk $dir",
  'last'   => "last_bk $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['net'];

// WHERE for bookings range
$wb = ["1=1"];
if ($startDate!=='') $wb[]="b.booking_date>='$startDate'";
if ($endDate!=='')   $wb[]="b.booking_date<='$endDate'";
if ($serviceF>0)     $wb[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=$serviceF)";
$wbSql = implode(" AND ", $wb);

// Aggregation per staff
$sql="
  SELECT
    s.staff_id, s.staff_name,
    SUM(1)                                   AS tx_total,
    SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END) AS tx_conf,
    SUM(CASE WHEN b.status={$completedStatus} THEN 1 ELSE 0 END) AS tx_comp,
    SUM(CASE WHEN b.status={$pendingStatus}   THEN 1 ELSE 0 END) AS tx_pend,
    SUM(CASE WHEN b.status={$cancelledStatus} THEN 1 ELSE 0 END) AS tx_canc,
    COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) AS net_rev,
    CASE WHEN SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END)=0
         THEN 0
         ELSE COALESCE(SUM(CASE WHEN b.status={$confirmedStatus} THEN b.final_price ELSE 0 END),0) /
              SUM(CASE WHEN b.status={$confirmedStatus} THEN 1 ELSE 0 END)
    END AS aov,
    COUNT(DISTINCT CASE WHEN b.status={$confirmedStatus} THEN b.customer_id END) AS custs,
    COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(b.time_end, b.time_start)))/3600,0) AS hrs,
    AVG(TIME_TO_SEC(TIMEDIFF(b.time_end, b.time_start))/60) AS avgdur,
    MIN(b.booking_date) AS first_bk,
    MAX(b.booking_date) AS last_bk
  FROM staff s
  LEFT JOIN booking b ON b.staff_id=s.staff_id AND $wbSql
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

$totalRows = count($rows);
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

// Dropdown options
$serviceOps = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");
$staffOps   = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name");
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
  <link rel="stylesheet" href="style.css">
  <style>
    .small-label{font-size:.9rem;color:#6c757d}
    .kpi-value{font-size:28px;font-weight:700;margin:0}
    .card-icon{font-size:28px}
    .table-sm td, .table-sm th { padding: .45rem .5rem; }
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

        <form class="row g-2 align-items-end" method="get">
          <input type="hidden" name="tab" value="table">
          <div class="col-md-auto">
            <label class="form-label small-label">ค้นหาสตาฟ</label>
            <input type="text" class="form-control" name="q" value="<?=esc($search)?>" placeholder="ชื่อสตาฟ">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">บริการ</label>
            <select class="form-select" name="service">
              <option value="0">ทั้งหมด</option>
              <?php while($sv=$serviceOps->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>" <?= $serviceF==$sv['service_id']?'selected':'' ?>><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">ช่วงวันที่</label>
            <div class="d-flex gap-2">
              <input type="date" class="form-control" name="start_date" value="<?=esc($startDate)?>">
              <input type="date" class="form-control" name="end_date"   value="<?=esc($endDate)?>">
            </div>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Sort by</label>
            <div class="input-group">
              <select class="form-select" name="sort">
                <option value="net"   <?= $sort==='net'?'selected':'' ?>>Net Revenue</option>
                <option value="tx"    <?= $sort==='tx'?'selected':'' ?>>Bookings (ทั้งหมด)</option>
                <option value="conf"  <?= $sort==='conf'?'selected':'' ?>>Confirmed</option>
                <option value="comp"  <?= $sort==='comp'?'selected':'' ?>>Complate</option>
                <option value="pend"  <?= $sort==='pend'?'selected':'' ?>>Pending</option>
                <option value="canc"  <?= $sort==='canc'?'selected':'' ?>>Cancelled</option>
                <option value="aov"   <?= $sort==='aov'?'selected':'' ?>>AOV</option>
                <option value="cust"  <?= $sort==='cust'?'selected':'' ?>>Distinct Customers</option>
                <option value="hrs"   <?= $sort==='hrs'?'selected':'' ?>>Hours</option>
                <option value="avgdur"<?= $sort==='avgdur'?'selected':'' ?>>Avg Duration</option>
                <option value="first" <?= $sort==='first'?'selected':'' ?>>First Booking</option>
                <option value="last"  <?= $sort==='last'?'selected':'' ?>>Last Booking</option>
                <option value="name"  <?= $sort==='name'?'selected':'' ?>>ชื่อสตาฟ</option>
              </select>
              <select class="form-select" name="dir">
                <option value="ASC"  <?= $dir==='ASC'?'selected':'' ?>>ASC</option>
                <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>DESC</option>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i> ใช้ตัวกรอง</button>
          </div>
                    <div class="ms-auto col-md-auto d-flex gap-2 align-items-center">
            <span class="badge bg-primary">สตาฟที่พบ: <?=number_format($totalRows)?> คน</span>
          </div>
        </form>

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
                  <td class="text-end"><?=number_format((int)$r['tx_total'])?></td>
                  <td class="text-end"><span class="badge bg-success"><?=number_format((int)$r['tx_conf'])?></span></td>
                  <td class="text-end"><span class="badge bg-primary"><?=number_format((int)$r['tx_comp'])?></span></td>
                  <td class="text-end"><span class="badge bg-warning text-dark"><?=number_format((int)$r['tx_pend'])?></span></td>
                  <td class="text-end"><span class="badge bg-danger"><?=number_format((int)$r['tx_canc'])?></span></td>
                  <td class="text-end fw-bold text-success">฿<?=number_format((float)$r['net_rev'],2)?></td>
                  <!-- <td class="text-end">฿<?=number_format((float)$r['aov'],2)?></td>
                  <td class="text-end"><?=number_format((int)$r['custs'])?></td> -->
                  <td class="text-end"><?=number_format($hrs,2)?></td>
                  <!-- <td class="text-end"><?=number_format($avgdur,1)?></td>
                  <td><?=esc($r['first_bk']?:'-')?></td>
                  <td><?=esc($r['last_bk']?:'-')?></td>
                  <td>
                    <div class="btn-group">
                      <a href="staff_detail.php?id=<?= (int)$r['staff_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Staff"><i class="bi bi-person"></i></a>
                      <a href="booking.php?staff=<?= (int)$r['staff_id'] ?>" class="btn btn-sm btn-outline-secondary" title="View Bookings"><i class="bi bi-calendar3"></i></a>
                    </div>
                  </td> -->
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

      </div></div>
    </div>

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="t-chart">
      <div class="card mt-3"><div class="card-body">

        <!-- KPI Cards (GLOBAL; not filtered) -->
        <div class="row g-3">
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-people card-icon text-primary"></i>
            <div><div class="kpi-value" id="k_staff">0</div><div class="text-muted">Total Staff</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-graph-up-arrow card-icon text-success"></i>
            <div><div class="kpi-value" id="k_rev">0</div><div class="text-muted">Net Revenue (confirmed)</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-receipt card-icon text-secondary"></i>
            <div><div class="kpi-value" id="k_tx">0</div><div class="text-muted">Confirmed Bookings</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body">
            <div class="small text-muted">Avg Net / Staff</div>
            <div class="kpi-value" id="k_avg_rev">0</div>
            <div class="small text-muted mt-2">Top Performer</div>
            <div><strong id="k_top_name">-</strong> <span class="text-success" id="k_top_net"></span></div>
          </div></div></div>
        </div>
        <div class="text-muted">* การ์ดไม่ใช้ตัวกรอง</div>

        <!-- Chart filters -->
        <div class="row g-3 align-items-end mt-2">
          <div class="col-md-auto">
            <label class="form-label small-label">ช่วงเวลา</label>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="period" id="p_all" value="all">
              <label class="btn btn-outline-primary" for="p_all">All</label>
              <input type="radio" class="btn-check" name="period" id="p_month" value="month" checked>
              <label class="btn btn-outline-primary" for="p_month">Month</label>
              <input type="radio" class="btn-check" name="period" id="p_year" value="year">
              <label class="btn btn-outline-primary" for="p_year">Year</label>
            </div>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">สตาฟ</label>
            <select id="chart_staff" class="form-select">
              <option value="0">ทั้งหมด</option>
              <?php while($s=$staffOps->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>"><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">บริการ</label>
            <select id="chart_service" class="form-select">
              <option value="0">ทั้งหมด</option>
              <?php $serviceOps2 = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name"); while($sv=$serviceOps2->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>"><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <!-- Period-specific controls -->
          <div class="col-md-auto" id="ctl-month">
            <label class="form-label small-label">เดือน/ปี</label>
            <div class="d-flex gap-2">
              <select id="month" class="form-select">
                <?php for($m=1;$m<=12;$m++): ?>
                  <option value="<?=$m?>" <?= $m==(int)date('n')?'selected':'' ?>><?=$m?></option>
                <?php endfor; ?>
              </select>
              <select id="year_m" class="form-select">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto d-none" id="ctl-year">
            <label class="form-label small-label">ปี</label>
            <select id="year_y" class="form-select">
              <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                <option value="<?=$y?>" <?= $y==(int)date('Y')?'selected':'' ?>><?=$y?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-auto d-none" id="ctl-all">
            <label class="form-label small-label">ช่วงปี</label>
            <div class="d-flex gap-2">
              <select id="start_year" class="form-select">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==$minYear?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
              <select id="end_year" class="form-select">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?=$y?>" <?= $y==$maxYear?'selected':'' ?>><?=$y?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="col-md-auto">
            <button id="applyChart" class="btn btn-primary"><i class="bi bi-funnel"></i> ใช้ตัวกรอง</button>
          </div>
        </div>

        <!-- Charts -->
        <div class="row mt-3 g-3">
          <div class="col-lg-8">
            <div class="card h-100"><div class="card-body">
              <h6 class="card-title">Bookings & Net by Time</h6>
              <canvas id="staffMainChart" height="120"></canvas>
              <div class="text-muted mt-2">* เส้น: Confirmed/Pending/Cancelled/Total | แท่ง: Net (confirmed)</div>
            </div></div>
          </div>
          <div class="col-lg-4">
            <div class="card h-100"><div class="card-body">
              <h6 class="card-title">Status Mix (ช่วงที่เลือก)</h6>
              <canvas id="mixChart" height="120"></canvas>
            </div></div>
          </div>
        </div>

        <div class="row mt-3 g-3">
          <div class="col-lg-6">
            <div class="card h-100"><div class="card-body">
              <h6 class="card-title">Top Staff by Net (ช่วงที่เลือก)</h6>
              <div class="table-responsive">
                <table class="table table-sm" id="tblTopNet">
                  <thead><tr><th>#</th><th>Staff</th><th class="text-end">Net</th><th class="text-end">Tx (conf)</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div></div>
          </div>
          <div class="col-lg-6">
            <div class="card h-100"><div class="card-body">
              <h6 class="card-title">Top Staff by Confirmed Tx (ช่วงที่เลือก)</h6>
              <div class="table-responsive">
                <table class="table table-sm" id="tblTopTx">
                  <thead><tr><th>#</th><th>Staff</th><th class="text-end">Tx (conf)</th><th class="text-end">Net</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div></div>
          </div>
        </div>

      </div></div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateCtl(){
  const p=document.querySelector('input[name=period]:checked')?.value||'month';
  document.getElementById('ctl-month').classList.toggle('d-none',p!=='month');
  document.getElementById('ctl-year').classList.toggle('d-none',p!=='year');
  document.getElementById('ctl-all').classList.toggle('d-none',p!=='all');
}
['p_all','p_month','p_year'].forEach(id=>document.getElementById(id).addEventListener('change',updateCtl));
updateCtl();

let mainChart, mixChart;
function fmt(x,dec=2){ return Number(x||0).toLocaleString(undefined,{minimumFractionDigits:dec,maximumFractionDigits:dec}); }

async function loadStats(){
  const p=document.querySelector('input[name=period]:checked')?.value||'month';
  const url=new URL(location.href); url.search=''; url.searchParams.set('action','stats'); url.searchParams.set('period',p);
  url.searchParams.set('staff',document.getElementById('chart_staff').value);
  url.searchParams.set('service',document.getElementById('chart_service').value);
  if(p==='month'){ url.searchParams.set('year',document.getElementById('year_m').value); url.searchParams.set('month',document.getElementById('month').value); }
  else if(p==='year'){ url.searchParams.set('year',document.getElementById('year_y').value); }
  else { url.searchParams.set('start_year',document.getElementById('start_year').value); url.searchParams.set('end_year',document.getElementById('end_year').value); }

  const res=await fetch(url.toString(),{cache:'no-store'});
  const data=await res.json();

  // KPIs
  document.getElementById('k_staff').textContent=(data.cards.total_staff||0).toLocaleString();
  document.getElementById('k_rev').textContent='฿'+fmt(data.cards.confirmed_rev);
  document.getElementById('k_tx').textContent=(data.cards.confirmed_tx||0).toLocaleString();
  document.getElementById('k_avg_rev').textContent='฿'+fmt(data.cards.avg_rev_per_staff);
  document.getElementById('k_top_name').textContent=data.cards.top?.name||'-';
  document.getElementById('k_top_net').textContent='฿'+fmt(data.cards.top?.net||0);

  // Main chart
  const labels=data.chart.labels, axis=data.chart.axis;
  const sT=data.chart.series.total, sC=data.chart.series.confirmed, sP=data.chart.series.pending, sX=data.chart.series.cancelled, sN=data.chart.series.net;
  if(mainChart) mainChart.destroy();
  const ctx=document.getElementById('staffMainChart').getContext('2d');
  mainChart=new Chart(ctx,{
    data:{
      labels,
      datasets:[
        {type:'line', label:'Confirmed', data:sC, tension:.3, pointRadius:3, borderWidth:2},
        {type:'line', label:'Pending',   data:sP, tension:.3, pointRadius:3, borderWidth:2},
        {type:'line', label:'Cancelled', data:sX, tension:.3, pointRadius:3, borderWidth:2},
        {type:'line', label:'Total',     data:sT, tension:.3, pointRadius:3, borderWidth:2},
        {type:'bar',  label:'Net (confirmed)', data:sN, borderWidth:1}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      scales:{ x:{ title:{display:true,text:axis} }, y:{ beginAtZero:true } },
      plugins:{ legend:{display:true}, tooltip:{mode:'index',intersect:false} }
    }
  });

  // Mix chart
  if(mixChart) mixChart.destroy();
  const m=data.status_mix||{confirmed:0,pending:0,cancelled:0};
  const mctx=document.getElementById('mixChart').getContext('2d');
  mixChart=new Chart(mctx,{
    type:'doughnut',
    data:{ labels:['Confirmed','Pending','Cancelled'], datasets:[{ data:[m.confirmed||0,m.pending||0,m.cancelled||0] }]},
    options:{ responsive:true, plugins:{ legend:{position:'top'} } }
  });

  // Top tables
  const tn=document.querySelector('#tblTopNet tbody'); tn.innerHTML='';
  (data.top.by_net||[]).forEach((r,i)=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`<td>${i+1}</td><td>${r.name||'-'}</td><td class="text-end">฿${fmt(r.net)}</td><td class="text-end">${(r.tx||0).toLocaleString()}</td>`;
    tn.appendChild(tr);
  });
  if(!tn.children.length){ tn.innerHTML='<tr><td colspan="4" class="text-center text-muted">No data</td></tr>'; }

  const tt=document.querySelector('#tblTopTx tbody'); tt.innerHTML='';
  (data.top.by_tx||[]).forEach((r,i)=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`<td>${i+1}</td><td>${r.name||'-'}</td><td class="text-end">${(r.tx||0).toLocaleString()}</td><td class="text-end">฿${fmt(r.net)}</td>`;
    tt.appendChild(tr);
  });
  if(!tt.children.length){ tt.innerHTML='<tr><td colspan="4" class="text-center text-muted">No data</td></tr>'; }
}

document.getElementById('applyChart').addEventListener('click', loadStats);
document.getElementById('chart-tab')?.addEventListener('shown.bs.tab', ()=> loadStats());
</script>
</body>
</html>