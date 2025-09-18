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
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// ----------------------- Year range meta for pickers -----------------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ======================= AJAX: KPIs + Chart =======================
if (isset($_GET['action']) && $_GET['action'] === 'stats') {
  header('Content-Type: application/json; charset=utf-8');

  $period = $_GET['period'] ?? 'month';       // all | year | month
  $metric = $_GET['metric'] ?? 'net';         // net | bookings
  $topN   = max(1, min(10, (int)($_GET['top'] ?? 5)));
  $year   = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
  $month  = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $startY = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $minYear;
  $endY   = isset($_GET['end_year'])   ? (int)$_GET['end_year']   : $maxYear;
  if ($startY > $endY) { $t=$startY; $startY=$endY; $endY=$t; }

  $staffId   = isset($_GET['staff'])   ? (int)$_GET['staff']   : 0;
  $serviceId = isset($_GET['service']) ? (int)$_GET['service'] : 0;

  // ---------- KPI (GLOBAL; confirmed bookings only) ----------
  $k = $conn->query("
    SELECT
      COALESCE(SUM(final_price),0)   AS net,
      COUNT(*)                       AS tx,
      COUNT(DISTINCT customer_id)    AS customers
    FROM booking
    WHERE status='confirmed'
  ")->fetch_assoc();
  $net_total = (float)$k['net'];
  $tx_total = (int)$k['tx'];
  $cust_total = (int)$k['customers'];
  $svc_total = (int)$conn->query("
    SELECT COUNT(DISTINCT so.service_id) c
    FROM booking b
    JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
    JOIN service_option so ON so.option_id=bs.option_id
    WHERE b.status='confirmed'
  ")->fetch_assoc()['c'];
  // top service (by net, all time)
  $top = $conn->query("
    SELECT sv.service_name, COALESCE(SUM(COALESCE(bs.net_price, bs.price_booking-COALESCE(bs.discount_booking,0), so.price)),0) AS net
    FROM booking b
    JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
    JOIN service_option so ON so.option_id=bs.option_id
    JOIN service sv ON sv.service_id=so.service_id
    WHERE b.status='confirmed'
    GROUP BY sv.service_id
    ORDER BY net DESC
    LIMIT 1
  ")->fetch_assoc();
  $top_service = $top['service_name'] ?? '-';
  $top_service_net = (float)($top['net'] ?? 0);

  // ---------- Build buckets ----------
  $labels=[]; $axis=''; $dateCond="1=1"; $rangeStart=""; $rangeEnd="";
  if ($period === 'month') {
    $days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
    for ($d=1;$d<=$days;$d++) $labels[] = str_pad((string)$d,2,'0',STR_PAD_LEFT);
    $bucketExpr = "DATE_FORMAT(b.booking_date,'%d')";
    $dateCond = "YEAR(b.booking_date)=$year AND MONTH(b.booking_date)=$month";
    $axis='วัน';
    $rangeStart=sprintf('%04d-%02d-01',$year,$month);
    $rangeEnd  =sprintf('%04d-%02d-%02d',$year,$month,$days);
  } elseif ($period === 'year') {
    $labels = ['01','02','03','04','05','06','07','08','09','10','11','12'];
    $bucketExpr = "DATE_FORMAT(b.booking_date,'%m')";
    $dateCond = "YEAR(b.booking_date)=$year";
    $axis='เดือน';
    $rangeStart=sprintf('%04d-01-01',$year);
    $rangeEnd  =sprintf('%04d-12-31',$year);
  } else {
    for ($y=$startY;$y<=$endY;$y++) $labels[]=(string)$y;
    $bucketExpr = "YEAR(b.booking_date)";
    $dateCond = "YEAR(b.booking_date) BETWEEN $startY AND $endY";
    $axis='ปี';
    $rangeStart=sprintf('%04d-01-01',$startY);
    $rangeEnd  =sprintf('%04d-12-31',$endY);
  }

  $extra = ["b.status='confirmed'"];
  if ($staffId > 0)   $extra[] = "b.staff_id=".$staffId;
  $whereBase = "$dateCond AND ".implode(" AND ",$extra);

  // ---------- Determine service list to plot ----------
  $svcList = [];
  if ($serviceId > 0) {
    $r = $conn->query("SELECT service_id, service_name FROM service WHERE service_id=$serviceId")->fetch_assoc();
    if ($r) $svcList = [ ['id'=>$r['service_id'], 'name'=>$r['service_name']] ];
  } else {
    // top N by net within range (+optional staff filter)
    $sqlTop = "
      SELECT sv.service_id, sv.service_name,
             COALESCE(SUM(COALESCE(bs.net_price, bs.price_booking-COALESCE(bs.discount_booking,0), so.price)),0) AS net
      FROM booking b
      JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      JOIN service_option so ON so.option_id=bs.option_id
      JOIN service sv ON sv.service_id=so.service_id
      WHERE $whereBase
      GROUP BY sv.service_id
      ORDER BY net DESC
      LIMIT $topN
    ";
    $rsTop = $conn->query($sqlTop);
    while($row=$rsTop->fetch_assoc()){ $svcList[]=['id'=>$row['service_id'],'name'=>$row['service_name']]; }
  }

  // ---------- Build dataset for each service ----------
  $datasets = [];
  foreach($svcList as $svc){
    if ($metric==='bookings') {
      $sqlS = "
        SELECT $bucketExpr AS bucket, COUNT(DISTINCT b.booking_id) AS val
        FROM booking b
        JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
        JOIN service_option so ON so.option_id=bs.option_id
        WHERE $whereBase AND so.service_id={$svc['id']}
        GROUP BY bucket ORDER BY bucket
      ";
    } else { // net
      $sqlS = "
        SELECT $bucketExpr AS bucket,
               COALESCE(SUM(COALESCE(bs.net_price, bs.price_booking-COALESCE(bs.discount_booking,0), so.price)),0) AS val
        FROM booking b
        JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
        JOIN service_option so ON so.option_id=bs.option_id
        WHERE $whereBase AND so.service_id={$svc['id']}
        GROUP BY bucket ORDER BY bucket
      ";
    }
    $map=[]; $rs=$conn->query($sqlS);
    while($row=$rs->fetch_assoc()){ $map[(string)$row['bucket']] = (float)$row['val']; }
    $arr=[]; foreach($labels as $b){ $arr[] = round($map[$b] ?? 0, 2); }
    $datasets[] = [ 'label'=>$svc['name'], 'data'=>$arr ];
  }

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
      'labels'=>$labels,
      'axis'=>$axis,
      'metric'=>$metric,
      'datasets'=>$datasets
    ],
    'range'=>['start'=>$rangeStart,'end'=>$rangeEnd]
  ]);
  exit;
}

// ======================= TABLE: filters + sort + pagination =======================
$q         = trim($_GET['q'] ?? '');
$status    = $_GET['status'] ?? 'confirmed';
$staffF    = isset($_GET['staff']) ? (int)$_GET['staff'] : 0;
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';
$sort      = $_GET['sort'] ?? 'net';
$dir       = strtoupper($_GET['dir'] ?? 'DESC'); $dir = $dir==='ASC'?'ASC':'DESC';

$sortMap=[
  'service'  => "sv.service_name $dir",
  'tx'       => "tx $dir",
  'customers'=> "customers $dir",
  'gross'    => "gross $dir",
  'discount' => "discount $dir",
  'net'      => "net $dir",
  'last'     => "last_booking $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['net'];

// WHERE for aggregated query
$where = ["b.status='confirmed'"]; $types=""; $params=[];
if ($q!==''){ $where[]="sv.service_name LIKE ?"; $kw="%$q%"; $params[]=$kw; $types.="s"; }
if ($staffF>0){ $where[]="b.staff_id=?"; $params[]=$staffF; $types.="i"; }
if ($startDate!==''){ $where[]="b.booking_date>=?"; $params[]=$startDate; $types.="s"; }
if ($endDate!==''){ $where[]="b.booking_date<=?"; $params[]=$endDate; $types.="s"; }
$whereSql = implode(" AND ",$where);

// Count distinct services for pagination
$sqlCount="
  SELECT COUNT(*) cnt FROM (
    SELECT sv.service_id
    FROM booking b
    JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
    JOIN service_option so ON so.option_id=bs.option_id
    JOIN service sv ON sv.service_id=so.service_id
    WHERE $whereSql
    GROUP BY sv.service_id
  ) t
";
$stmt=$conn->prepare($sqlCount);
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute(); $total=(int)$stmt->get_result()->fetch_assoc()['cnt']; $stmt->close();

$page=max(1,(int)($_GET['page']??1)); $per=20; $off=($page-1)*$per;

// Aggregated list per service
$sqlList="
  SELECT
    sv.service_id, sv.service_name,
    COUNT(DISTINCT b.booking_id) AS tx,
    COUNT(DISTINCT b.customer_id) AS customers,
    COALESCE(SUM(COALESCE(bs.price_booking, so.price)),0)                         AS gross,
    COALESCE(SUM(COALESCE(bs.discount_booking,0)),0)                               AS discount,
    COALESCE(SUM(COALESCE(bs.net_price, bs.price_booking-COALESCE(bs.discount_booking,0), so.price)),0) AS net,
    MAX(b.booking_date) AS last_booking
  FROM booking b
  JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
  JOIN service_option so ON so.option_id=bs.option_id
  JOIN service sv ON sv.service_id=so.service_id
  WHERE $whereSql
  GROUP BY sv.service_id
  ORDER BY $orderBy
  LIMIT ? OFFSET ?
";
$params2=$params; $types2=$types."ii"; $params2[]=$per; $params2[]=$off;
$stmt=$conn->prepare($sqlList);
$stmt->bind_param($types2,...$params2);
$stmt->execute(); $rs=$stmt->get_result();
$rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r; $stmt->close();

// Dropdowns
$staffOps = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name");
$serviceOps = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");
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

        <form class="row g-2 align-items-end" method="get">
          <input type="hidden" name="tab" value="table">
          <div class="col-md-auto">
            <label class="form-label small-label">ค้นหาบริการ</label>
            <input type="text" class="form-control" name="q" value="<?=esc($q)?>" placeholder="ชื่อบริการ">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">สถานะ</label>
            <select class="form-select" name="status" disabled>
              <option value="confirmed" selected>confirmed (ยึดสำหรับรายได้)</option>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">พนักงาน</label>
            <select class="form-select" name="staff">
              <option value="0">ทั้งหมด</option>
              <?php while($s=$staffOps->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>" <?= $staffF==$s['staff_id']?'selected':'' ?>><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">วันที่</label>
            <div class="d-flex gap-2">
              <input type="date" class="form-control" name="start_date" value="<?=esc($startDate)?>">
              <input type="date" class="form-control" name="end_date"   value="<?=esc($endDate)?>">
            </div>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Sort by</label>
            <div class="input-group">
              <select class="form-select" name="sort">
                <option value="net"       <?= $sort==='net'?'selected':'' ?>>Net</option>
                <option value="tx"        <?= $sort==='tx'?'selected':'' ?>>Transactions</option>
                <option value="customers" <?= $sort==='customers'?'selected':'' ?>>Customers</option>
                <option value="gross"     <?= $sort==='gross'?'selected':'' ?>>Gross</option>
                <option value="discount"  <?= $sort==='discount'?'selected':'' ?>>Discount</option>
                <option value="last"      <?= $sort==='last'?'selected':'' ?>>Last Booking</option>
                <option value="service"   <?= $sort==='service'?'selected':'' ?>>Service Name</option>
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
            <span class="badge bg-primary">บริการที่พบ: <?=number_format($total)?></span>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Service</th>
                <th class="text-end">Tx</th>
                <th class="text-end">Customers</th>
                <th class="text-end">Gross</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Net</th>
                <th>Last Booking</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="7" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $r): ?>
                <tr>
                  <td><?=esc($r['service_name'])?></td>
                  <td class="text-end"><?=number_format((int)$r['tx'])?></td>
                  <td class="text-end"><?=number_format((int)$r['customers'])?></td>
                  <td class="text-end"><?=number_format((float)$r['gross'],2)?></td>
                  <td class="text-end text-danger">-<?=number_format((float)$r['discount'],2)?></td>
                  <td class="text-end fw-bold text-success"><?=number_format((float)$r['net'],2)?></td>
                  <td><small class="text-muted"><?=esc($r['last_booking'])?></small></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

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
        <div class="row g-3 align-items-end">
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
            <label class="form-label small-label">Metric</label>
            <select id="metric" class="form-select">
              <option value="net" selected>Net Revenue</option>
              <option value="bookings">Bookings Count</option>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">พนักงาน</label>
            <select id="chart_staff" class="form-select">
              <option value="0">ทั้งหมด</option>
              <?php $staffOps2 = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name"); while($s=$staffOps2->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>"><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">บริการ</label>
            <select id="chart_service" class="form-select">
              <option value="0">Top N</option>
              <?php $serviceOps2 = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name"); while($sv=$serviceOps2->fetch_assoc()): ?>
                <option value="<?=$sv['service_id']?>"><?=esc($sv['service_name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-auto" id="ctl-topn">
            <label class="form-label small-label">Top N</label>
            <select id="topN" class="form-select">
              <option>3</option><option selected>5</option><option>8</option><option>10</option>
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

        <!-- Chart -->
        <div class="mt-3" style="min-height:380px">
          <canvas id="svcChart" height="120"></canvas>
        </div>
        <div class="text-muted mt-2">* เส้นหลายสี: บริการแต่ละตัว (Top N หรือบริการที่เลือก) — คลิก legend เพื่อเปิด–ปิดเส้น</div>

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
document.getElementById('chart_service').addEventListener('change',()=>{
  const v=document.getElementById('chart_service').value;
  document.getElementById('ctl-topn').classList.toggle('d-none', v!=='0');
});

let chart;
function fmtMoney(x){ return Number(x||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }

async function loadStats(){
  const p=document.querySelector('input[name=period]:checked')?.value||'month';
  const url=new URL(location.href); url.search=''; url.searchParams.set('action','stats'); url.searchParams.set('period',p);
  url.searchParams.set('metric',document.getElementById('metric').value);
  url.searchParams.set('staff',document.getElementById('chart_staff').value);
  url.searchParams.set('service',document.getElementById('chart_service').value);
  if(document.getElementById('chart_service').value==='0'){
    url.searchParams.set('top',document.getElementById('topN').value);
  }
  if(p==='month'){ url.searchParams.set('year',document.getElementById('year_m').value); url.searchParams.set('month',document.getElementById('month').value); }
  else if(p==='year'){ url.searchParams.set('year',document.getElementById('year_y').value); }
  else { url.searchParams.set('start_year',document.getElementById('start_year').value); url.searchParams.set('end_year',document.getElementById('end_year').value); }

  const res=await fetch(url.toString(),{cache:'no-store'});
  const data=await res.json();

  // KPIs
  document.getElementById('k_net_total').textContent = fmtMoney(data.cards.net_total);
  document.getElementById('k_tx_total').textContent = (data.cards.tx_total||0).toLocaleString();
  document.getElementById('k_customers_total').textContent = (data.cards.customers_total||0).toLocaleString();
  document.getElementById('k_services_total').textContent = (data.cards.services_sold||0).toLocaleString();
  document.getElementById('k_top_service').textContent = data.cards.top_service || '-';
  document.getElementById('k_top_service_net').textContent = fmtMoney(data.cards.top_service_net||0);

  // Chart
  if(chart) chart.destroy();
  const ctx=document.getElementById('svcChart').getContext('2d');
  chart=new Chart(ctx,{
    type:'line',
    data:{
      labels: data.chart.labels,
      datasets: (data.chart.datasets||[]).map((d,idx)=> ({
        label:d.label, data:d.data, tension:.3, pointRadius:2, borderWidth:2
      }))
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      scales:{ x:{ title:{display:true,text:data.chart.axis} }, y:{ beginAtZero:true } },
      plugins:{ legend:{display:true}, tooltip:{mode:'index', intersect:false} }
    }
  });
}

document.getElementById('applyChart').addEventListener('click', loadStats);
document.getElementById('chart-tab')?.addEventListener('shown.bs.tab', ()=> loadStats());
</script>
</body>
</html>