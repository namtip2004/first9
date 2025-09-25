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

  $period = $_GET['period'] ?? 'month'; // all|month|year
  $year   = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
  $month  = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $startY = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $minYear;
  $endY   = isset($_GET['end_year'])   ? (int)$_GET['end_year']   : $maxYear;

  $staff  = isset($_GET['staff'])  ? (int)$_GET['staff'] : 0;
  $service= isset($_GET['service'])? (int)$_GET['service'] : 0;

  // KPI cards (GLOBAL; not affected by chart filters)
  $k_total = (int)$conn->query("SELECT COUNT(*) c FROM booking")->fetch_assoc()['c'];
  $k_conf  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_CONFIRMED))->fetch_assoc()['c'];
  $k_pend  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_PENDING))->fetch_assoc()['c'];
  $k_canc  = (int)$conn->query(sprintf("SELECT COUNT(*) c FROM booking WHERE status=%d", BOOKING_STATUS_CANCELLED))->fetch_assoc()['c'];

  // Chart bucketing
  $labels=[]; $groupExpr=""; $dateFilter="1=1"; $axis='';
  if($period==='month'){
    $days=cal_days_in_month(CAL_GREGORIAN,$month,$year);
    for($d=1;$d<=$days;$d++) $labels[]=str_pad((string)$d,2,'0',STR_PAD_LEFT);
    $groupExpr="DATE_FORMAT(b.booking_date,'%d')";
    $dateFilter="YEAR(b.booking_date)=$year AND MONTH(b.booking_date)=$month";
    $axis='วัน';
  }elseif($period==='year'){
    $labels=['01','02','03','04','05','06','07','08','09','10','11','12'];
    $groupExpr="DATE_FORMAT(b.booking_date,'%m')";
    $dateFilter="YEAR(b.booking_date)=$year";
    $axis='เดือน';
  }else{ // all
    for($y=$startY;$y<=$endY;$y++) $labels[]=(string)$y;
    $groupExpr="YEAR(b.booking_date)";
    $dateFilter="YEAR(b.booking_date) BETWEEN $startY AND $endY";
    $axis='ปี';
  }

  // Optional filters (staff, service)
  $w=[]; $w[]=$dateFilter;
  if($staff>0)   $w[]="b.staff_id=".$staff;
  if($service>0) $w[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=".$service.")";
  $where=implode(" AND ",$w);

  $confStatus = BOOKING_STATUS_CONFIRMED;
  $pendStatus = BOOKING_STATUS_PENDING;
  $cancStatus = BOOKING_STATUS_CANCELLED;

  $sql="
    SELECT $groupExpr AS b,
           SUM(CASE WHEN b.status=$confStatus THEN 1 ELSE 0 END) AS conf,
           SUM(CASE WHEN b.status=$pendStatus THEN 1 ELSE 0 END) AS pend,
           SUM(CASE WHEN b.status=$cancStatus THEN 1 ELSE 0 END) AS canc,
           COUNT(*) AS total
    FROM booking b
    WHERE $where
    GROUP BY b
    ORDER BY b
  ";
  $rs=$conn->query($sql);
  $mapT=[]; $mapC=[]; $mapP=[]; $mapX=[];
  while($row=$rs->fetch_assoc()){
    $k=(string)$row['b'];
    $mapT[$k]=(int)$row['total'];
    $mapC[$k]=(int)$row['conf'];
    $mapP[$k]=(int)$row['pend'];
    $mapX[$k]=(int)$row['canc'];
  }
  $dataT=[]; $dataC=[]; $dataP=[]; $dataX=[];
  foreach($labels as $b){
    $dataT[]=$mapT[$b]??0;
    $dataC[]=$mapC[$b]??0;
    $dataP[]=$mapP[$b]??0;
    $dataX[]=$mapX[$b]??0;
  }

  echo json_encode([
    'cards'=>[
      'total'=>$k_total,'confirmed'=>$k_conf,'pending'=>$k_pend,'cancelled'=>$k_canc
    ],
    'chart'=>[
      'labels'=>$labels,
      'axis'=>$axis,
      'series'=>[
        'total'=>$dataT,'confirmed'=>$dataC,'pending'=>$dataP,'cancelled'=>$dataX
      ]
    ]
  ]);
  exit;
}

// ----------------------- TABLE: filters + sort + pagination -----------------------
$search = trim($_GET['q'] ?? '');
$statusParam = $_GET['status'] ?? 'all';
$statusCode = $statusParam === 'all' ? null : booking_status_code($statusParam);
$statusValue = $statusCode === null ? 'all' : (string)$statusCode;
$staffF = isset($_GET['staff']) ? (int)$_GET['staff'] : 0;
$serviceF = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';
$sort   = $_GET['sort'] ?? 'date';
$dir    = strtoupper($_GET['dir'] ?? 'DESC'); $dir=$dir==='ASC'?'ASC':'DESC';

$sortMap=[
  'date'    => "b.booking_date $dir, b.time_start $dir",
  'price'   => "b.final_price $dir",
  'status'  => "b.status $dir",
  'customer'=> "c.customer_name $dir",
  'staff'   => "s.staff_name $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['date'];

// WHERE conditions (base)
$where = ["1=1"]; $types=""; $params=[];
if($search!==''){
  $where[]="(c.customer_name LIKE ? OR c.gmail LIKE ? OR s.staff_name LIKE ?)";
  $kw="%$search%"; $params[]=$kw; $params[]=$kw; $params[]=$kw; $types.="sss";
}
if($statusCode !== null){
  $where[]="b.status=?"; $params[]=$statusCode; $types.="i";
}
if($staffF>0){
  $where[]="b.staff_id=?"; $params[]=$staffF; $types.="i";
}
if($startDate!==''){
  $where[]="b.booking_date>=?"; $params[]=$startDate; $types.="s";
}
if($endDate!==''){
  $where[]="b.booking_date<=?"; $params[]=$endDate; $types.="s";
}
// Service filter via EXISTS (avoid row dup)
if($serviceF>0){
  $where[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=?)";
  $params[]=$serviceF; $types.="i";
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
    b.booking_id, b.booking_date, b.time_start, b.time_end, b.final_price, b.status,
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
  <title>Booking Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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

  <div class="pagetitle"><h1>รายงานการจอง (Booking)</h1></div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-table" type="button" role="tab">ตาราง</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-chart" type="button" role="tab" id="chart-tab">กราฟ</button></li>
  </ul>

  <div class="tab-content">
    <!-- TAB: TABLE -->
    <div class="tab-pane fade show active" id="t-table" role="tabpanel">
      <div class="card mt-3"><div class="card-body">

        <form class="row g-2 align-items-end" method="get">
          <input type="hidden" name="tab" value="table">
          <div class="col-md-auto">
            <label class="form-label small-label">ค้นหา</label>
            <input type="text" class="form-control" name="q" value="<?=esc($search)?>" placeholder="ลูกค้า/อีเมล/พนักงาน">
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">สถานะ</label>
            <select class="form-select" name="status">
              <?php
                $statusOptions = ['all' => 'ทั้งหมด'];
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
            <label class="form-label small-label">พนักงาน</label>
            <select class="form-select" name="staff">
              <option value="0">ทั้งหมด</option>
              <?php while($s=$staffOps->fetch_assoc()): ?>
                <option value="<?=$s['staff_id']?>" <?= $staffF==$s['staff_id']?'selected':'' ?>><?=esc($s['staff_name'])?></option>
              <?php endwhile; ?>
            </select>
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
                <option value="date"     <?= $sort==='date'?'selected':'' ?>>วันที่จอง</option>
                <option value="price"    <?= $sort==='price'?'selected':'' ?>>ราคา</option>
                <option value="status"   <?= $sort==='status'?'selected':'' ?>>สถานะ</option>
                <option value="customer" <?= $sort==='customer'?'selected':'' ?>>ลูกค้า</option>
                <option value="staff"    <?= $sort==='staff'?'selected':'' ?>>พนักงาน</option>
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
          <div class="ms-auto col-md-auto">
            <span class="badge bg-primary">รวม: <?=number_format($total)?> รายการ</span>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle" id="bookingTable">
            <thead class="table-light">
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Services</th>
                <th>Staff</th>
                <th class="text-end">Final Price</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="9" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $bk): ?>
                <tr>
                  <td><?=esc($bk['booking_id'])?></td>
                  <td>
                    <?=esc($bk['customer_name']?:'N/A')?><br>
                    <small class="text-muted"><?=esc($bk['gmail']?:'-')?></small>
                  </td>
                  <td><?=esc($bk['booking_date'])?></td>
                  <td><?=esc(substr($bk['time_start'],0,5))?>–<?=esc(substr($bk['time_end'],0,5))?></td>
                  <td><?=esc($bk['services']?:'N/A')?></td>
                  <td><?=esc($bk['staff_name']?:'N/A')?></td>
                  <td class="text-end text-success fw-bold">฿<?=number_format((float)$bk['final_price'],2)?></td>
                  <td>
                    <?php $stCode = booking_status_code($bk['status']); $badgeClass = booking_status_badge_class($stCode); $label = booking_status_label($stCode); ?>
                    <span class="badge <?=$badgeClass?>"><?=esc($label ?: 'N/A')?></span>
                  </td>
                  <td>
                    <div class="btn-group">
                      <a href="booking_detail.php?id=<?= (int)$bk['booking_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
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
    <div class="tab-pane fade" id="t-chart" role="tabpanel">
      <div class="card mt-3"><div class="card-body">

        <!-- Chart filters -->
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
        <div class="text-muted">* การ์ดไม่ใช้ตัวกรอง</div>

        <!-- CHART -->
        <div class="mt-3" style="min-height:360px">
          <canvas id="bkChart" height="120"></canvas>
        </div>
        <div class="text-muted mt-2">* กราฟเส้น: Total / Confirmed / Pending / Cancelled — เปิด–ปิดเส้นได้จาก Legend</div>

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

let chart;
async function loadStats(){
  const p=document.querySelector('input[name=period]:checked')?.value||'month';
  const url=new URL(location.href); url.search=''; url.searchParams.set('action','stats'); url.searchParams.set('period',p);
  url.searchParams.set('staff',document.getElementById('chart_staff').value);
  url.searchParams.set('service',document.getElementById('chart_service').value);
  if(p==='month'){ url.searchParams.set('year',document.getElementById('year_m').value); url.searchParams.set('month',document.getElementById('month').value); }
  else if(p==='year'){ url.searchParams.set('year',document.getElementById('year_y').value); }
  else { url.searchParams.set('start_year',document.getElementById('start_year').value); url.searchParams.set('end_year',document.getElementById('end_year').value); }

  const res=await fetch(url.toString(),{cache:'no-store'}); const data=await res.json();

  // KPIs (global)
  document.getElementById('k_total').textContent=(data.cards.total||0).toLocaleString();
  document.getElementById('k_conf').textContent=(data.cards.confirmed||0).toLocaleString();
  document.getElementById('k_pend').textContent=(data.cards.pending||0).toLocaleString();
  document.getElementById('k_canc').textContent=(data.cards.cancelled||0).toLocaleString();

  // Chart
  const labels=data.chart.labels, axis=data.chart.axis;
  const sT=data.chart.series.total, sC=data.chart.series.confirmed, sP=data.chart.series.pending, sX=data.chart.series.cancelled;

  if(chart) chart.destroy();
  const ctx=document.getElementById('bkChart').getContext('2d');
  chart=new Chart(ctx,{
    type:'line',
    data:{
      labels,
      datasets:[
        {label:'Total',     data:sT, tension:.3, pointRadius:3, borderWidth:2},
        {label:'Confirmed', data:sC, tension:.3, pointRadius:3, borderWidth:2},
        {label:'Pending',   data:sP, tension:.3, pointRadius:3, borderWidth:2},
        {label:'Cancelled', data:sX, tension:.3, pointRadius:3, borderWidth:2}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      scales:{ x:{ title:{display:true,text:axis} }, y:{ beginAtZero:true, ticks:{ precision:0 } } },
      plugins:{ legend:{display:true}, tooltip:{mode:'index', intersect:false} }
    }
  });
}

document.getElementById('applyChart').addEventListener('click',loadStats);
document.getElementById('chart-tab')?.addEventListener('shown.bs.tab',()=>loadStats());
</script>
</body>
</html>