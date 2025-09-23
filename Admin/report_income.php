<?php
// report_income.php — Income report (2 tabs: Table + Chart). KPIs are global (not filtered).
// Assumes schema: booking(final_price,total_price,total_discount,status,booking_date,time_start,time_end,staff_id,customer_id),
// customer(customer_id,customer_name,gmail), staff(staff_id,staff_name)
// Optional service linkage: booking_seviceop(option_id, booking_id) -> service_option(option_id, service_id) -> service(service_id, service_name)

require_once("connect_db.php");
$confirmedStatus = BOOKING_STATUS_CONFIRMED;
$pendingStatus   = BOOKING_STATUS_PENDING;
$cancelledStatus = BOOKING_STATUS_CANCELLED;
function esc($s){return htmlspecialchars($s??'',ENT_QUOTES,'UTF-8');}

// ----------------------- Year range meta for chart pickers -----------------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ======================= AJAX: stats for KPI + chart =======================
if(isset($_GET['action']) && $_GET['action']==='stats'){
  header('Content-Type: application/json; charset=utf-8');

  $period = $_GET['period'] ?? 'month';    // all|month|year
  $year   = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
  $month  = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $startY = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $minYear;
  $endY   = isset($_GET['end_year'])   ? (int)$_GET['end_year']   : $maxYear;

  $staff  = isset($_GET['staff'])  ? (int)$_GET['staff'] : 0;
  $service= isset($_GET['service'])? (int)$_GET['service'] : 0;

  // ---------- KPI (GLOBAL; confirmed only) ----------
  $k_net_total = (float)$conn->query("SELECT COALESCE(SUM(final_price),0) s FROM booking WHERE status={$confirmedStatus}")->fetch_assoc()['s'];
  $k_gross_total = (float)$conn->query("SELECT COALESCE(SUM(total_price),0) s FROM booking WHERE status={$confirmedStatus}")->fetch_assoc()['s'];
  $k_disc_total = (float)$conn->query("SELECT COALESCE(SUM(total_discount),0) s FROM booking WHERE status={$confirmedStatus}")->fetch_assoc()['s'];
  $ym = date('Y-m');
  $k_net_mtd = (float)$conn->query("SELECT COALESCE(SUM(final_price),0) s FROM booking WHERE status={$confirmedStatus} AND DATE_FORMAT(booking_date,'%Y-%m')='$ym'")->fetch_assoc()['s'];

  // ---------- Chart bucketing ----------
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

  // confirmed revenue only; optional staff/service filters
  $w=[];
  $w[]=$dateFilter;
  $w[]="b.status={$confirmedStatus}";
  if($staff>0)   $w[]="b.staff_id=".$staff;
  if($service>0) $w[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=".$service.")";
  $where=implode(" AND ",$w);

  $sql="
    SELECT $groupExpr AS b,
           COALESCE(SUM(b.total_price),0)      AS gross,
           COALESCE(SUM(b.total_discount),0)   AS discount,
           COALESCE(SUM(b.final_price),0)      AS net,
           COUNT(*)                            AS n
    FROM booking b
    WHERE $where
    GROUP BY b
    ORDER BY b
  ";
  $rs=$conn->query($sql);
  $mapG=[]; $mapD=[]; $mapN=[]; $mapC=[];
  while($row=$rs->fetch_assoc()){
    $k=(string)$row['b'];
    $mapG[$k]=(float)$row['gross'];
    $mapD[$k]=(float)$row['discount'];
    $mapN[$k]=(float)$row['net'];
    $mapC[$k]=(int)$row['n'];
  }
  $seriesGross=[]; $seriesDisc=[]; $seriesNet=[]; $seriesCnt=[];
  foreach($labels as $b){
    $seriesGross[] = round($mapG[$b] ?? 0, 2);
    $seriesDisc[]  = round($mapD[$b] ?? 0, 2);
    $seriesNet[]   = round($mapN[$b] ?? 0, 2);
    $seriesCnt[]   = (int)($mapC[$b] ?? 0);
  }

  echo json_encode([
    'cards'=>[
      'net_total'   => $k_net_total,
      'net_mtd'     => $k_net_mtd,
      'gross_total' => $k_gross_total,
      'disc_total'  => $k_disc_total
    ],
    'chart'=>[
      'labels'=>$labels, 'axis'=>$axis,
      'series'=>[
        'gross'=>$seriesGross, 'discount'=>$seriesDisc, 'net'=>$seriesNet, 'count'=>$seriesCnt
      ]
    ]
  ]);
  exit;
}

// ======================= TABLE: filters + sort + pagination =======================
$search = trim($_GET['q'] ?? '');
$statusParam = $_GET['status'] ?? (string)$confirmedStatus; // default confirmed for income
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
  'customer'=> "c.customer_name $dir",
  'staff'   => "s.staff_name $dir",
  'gross'   => "b.total_price $dir",
  'disc'    => "b.total_discount $dir",
  'net'     => "b.final_price $dir",
  'status'  => "b.status $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['date'];

// WHERE
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
if($serviceF>0){
  $where[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=?)";
  $params[]=$serviceF; $types.="i";
}
$whereSql = implode(" AND ",$where);

// Count
$sqlCount="SELECT COUNT(*) cnt
           FROM booking b
           LEFT JOIN customer c ON c.customer_id=b.customer_id
           LEFT JOIN staff s ON s.staff_id=b.staff_id
           WHERE $whereSql";
$stmt=$conn->prepare($sqlCount);
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute(); $total=(int)$stmt->get_result()->fetch_assoc()['cnt']; $stmt->close();

// Totals (filtered, all rows)
$sqlTotals="SELECT 
              COALESCE(SUM(b.total_price),0)   AS gross_sum,
              COALESCE(SUM(b.total_discount),0)AS disc_sum,
              COALESCE(SUM(b.final_price),0)   AS net_sum
            FROM booking b
            LEFT JOIN customer c ON c.customer_id=b.customer_id
            LEFT JOIN staff s ON s.staff_id=b.staff_id
            WHERE $whereSql";
$stmt=$conn->prepare($sqlTotals);
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute(); $trow=$stmt->get_result()->fetch_assoc(); $stmt->close();

// Pagination
$page=max(1,(int)($_GET['page']??1)); $per=20; $off=($page-1)*$per;

// List
$sqlList="
  SELECT 
    b.booking_id, b.booking_date, b.time_start, b.time_end,
    b.total_price, b.total_discount, b.final_price, b.status,
    c.customer_name, c.gmail, s.staff_name,
    GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', ') AS services
  FROM booking b
  LEFT JOIN customer c ON b.customer_id=c.customer_id
  LEFT JOIN staff s ON b.staff_id=s.staff_id
  LEFT JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
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

// Dropdowns
$staffOps   = $conn->query("SELECT staff_id, staff_name FROM staff ORDER BY staff_name");
$serviceOps = $conn->query("SELECT service_id, service_name FROM service ORDER BY service_name");
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Income Report</title>
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

  <div class="pagetitle"><h1>รายงานรายได้ (Income)</h1></div>

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
                <option value="date"     <?= $sort==='date'?'selected':'' ?>>วันที่</option>
                <option value="customer" <?= $sort==='customer'?'selected':'' ?>>ลูกค้า</option>
                <option value="staff"    <?= $sort==='staff'?'selected':'' ?>>พนักงาน</option>
                <option value="gross"    <?= $sort==='gross'?'selected':'' ?>>Gross</option>
                <option value="disc"     <?= $sort==='disc'?'selected':'' ?>>Discount</option>
                <option value="net"      <?= $sort==='net'?'selected':'' ?>>Net</option>
                <option value="status"   <?= $sort==='status'?'selected':'' ?>>สถานะ</option>
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
            <span class="badge bg-primary">รายการ: <?=number_format($total)?></span>
            <span class="badge bg-success">Net (filter): ฿<?=number_format((float)$trow['net_sum'],2)?></span>
          </div>
        </form>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Services</th>
                <th>Staff</th>
                <th class="text-end">Gross</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Net</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="11" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $r): ?>
                <tr>
                  <td><?=esc($r['booking_id'])?></td>
                  <td><?=esc($r['customer_name']?:'N/A')?><br><small class="text-muted"><?=esc($r['gmail']?:'-')?></small></td>
                  <td><?=esc($r['booking_date'])?></td>
                  <td><?=esc(substr($r['time_start'],0,5))?>–<?=esc(substr($r['time_end'],0,5))?></td>
                  <td><?=esc($r['services']?:'N/A')?></td>
                  <td><?=esc($r['staff_name']?:'N/A')?></td>
                  <td class="text-end"><?=number_format((float)$r['total_price'],2)?></td>
                  <td class="text-end text-danger">-<?=number_format((float)$r['total_discount'],2)?></td>
                  <td class="text-end fw-bold text-success"><?=number_format((float)$r['final_price'],2)?></td>
                  <td>
                    <?php $stCode = booking_status_code($r['status']); $badgeClass = booking_status_badge_class($stCode); $label = booking_status_label($stCode); ?>
                    <span class="badge <?=$badgeClass?>"><?=esc($label)?></span>
                  </td>
                  <td>
                    <div class="btn-group">
                      <a href="booking_detail.php?id=<?= (int)$r['booking_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <?php if(!empty($rows)): ?>
            <tfoot>
              <tr class="table-light">
                <th colspan="6" class="text-end">รวม (ตามตัวกรองทั้งหมด)</th>
                <th class="text-end"><?=number_format((float)$trow['gross_sum'],2)?></th>
                <th class="text-end text-danger">-<?=number_format((float)$trow['disc_sum'],2)?></th>
                <th class="text-end text-success"><?=number_format((float)$trow['net_sum'],2)?></th>
                <th colspan="2"></th>
              </tr>
            </tfoot>
            <?php endif; ?>
          </table>
        </div>

      </div></div>
    </div>

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="t-chart">
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

        <!-- KPI (GLOBAL) -->
        <div class="row mt-3">
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-cash-coin card-icon text-success"></i>
            <div><div class="kpi-value" id="k_net_total">0</div><div class="text-muted">Net รวม (ยืนยันแล้ว)</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-graph-up card-icon text-primary"></i>
            <div><div class="kpi-value" id="k_net_mtd">0</div><div class="text-muted">Net เดือนนี้</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-receipt card-icon text-secondary"></i>
            <div><div class="kpi-value" id="k_gross_total">0</div><div class="text-muted">Gross รวม</div></div>
          </div></div></div>
          <div class="col-md-3"><div class="card"><div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-tag card-icon text-danger"></i>
            <div><div class="kpi-value" id="k_disc_total">0</div><div class="text-muted">ส่วนลดรวม</div></div>
          </div></div></div>
        </div>
        <div class="text-muted">* การ์ดไม่ใช้ตัวกรอง</div>

        <!-- CHART -->
        <div class="mt-3" style="min-height:380px">
          <canvas id="incomeChart" height="120"></canvas>
        </div>
        <div class="text-muted mt-2">* แท่ง: Gross / Discount / Net (เฉพาะ Confirmed) &nbsp;—&nbsp; คลิก legend เพื่อเปิด–ปิดชุดข้อมูล</div>

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

  const res=await fetch(url.toString(),{cache:'no-store'});
  const data=await res.json();

  // KPIs
  const nf=(v)=>Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('k_net_total').textContent  = nf(data.cards.net_total);
  document.getElementById('k_net_mtd').textContent    = nf(data.cards.net_mtd);
  document.getElementById('k_gross_total').textContent= nf(data.cards.gross_total);
  document.getElementById('k_disc_total').textContent = nf(data.cards.disc_total);

  // Chart
  const labels=data.chart.labels, axis=data.chart.axis;
  const sG=data.chart.series.gross, sD=data.chart.series.discount, sN=data.chart.series.net;

  if(chart) chart.destroy();
  const ctx=document.getElementById('incomeChart').getContext('2d');
  chart=new Chart(ctx,{
    type:'bar',
    data:{
      labels,
      datasets:[
        {label:'Gross',    data:sG, borderWidth:1},
        {label:'Discount', data:sD, borderWidth:1},
        {label:'Net',      data:sN, borderWidth:1}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      scales:{ x:{ title:{display:true,text:axis} }, y:{ beginAtZero:true } },
      plugins:{ legend:{display:true}, tooltip:{mode:'index', intersect:false} }
    }
  });
}

document.getElementById('applyChart').addEventListener('click',loadStats);
document.getElementById('chart-tab')?.addEventListener('shown.bs.tab',()=>loadStats());
</script>
</body>
</html>