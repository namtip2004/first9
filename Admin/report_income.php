<?php
// report_income.php — Income report (2 tabs: Table + Chart). KPIs are global (not filtered).
// Assumes schema: booking(final_price,total_price,total_discount,status,booking_date,time_start,time_end,staff_id,customer_id),
// customer(customer_id,customer_name,gmail), staff(staff_id,staff_name)
// Optional service linkage: booking_seviceop(option_id, booking_id) -> service_option(option_id, service_id) -> service(service_id, service_name)

require_once("connect_db.php");
$confirmedStatus = BOOKING_STATUS_CONFIRMED;
$completedStatus = BOOKING_STATUS_COMPLATE;
function esc($s){return htmlspecialchars($s??'',ENT_QUOTES,'UTF-8');}

// ----------------------- Year range meta for chart pickers -----------------------
$yr = $conn->query("SELECT MIN(YEAR(booking_date)) AS miny, MAX(YEAR(booking_date)) AS maxy FROM booking")->fetch_assoc();
$minYear = (int)($yr['miny'] ?? date('Y'));
$maxYear = (int)($yr['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ======================= AJAX: stats for KPI + chart =======================
if(isset($_GET['action']) && $_GET['action']==='stats'){
  header('Content-Type: application/json; charset=utf-8');

  $view = $_GET['view'] ?? 'years';
  $view = in_array($view, ['years','months','month_table'], true) ? $view : 'years';
  $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
  $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

  // KPI cards (confirmed + completed)
  $cardWhere = sprintf('status IN (%d,%d)', $confirmedStatus, $completedStatus);
  $k_net_total = (float)$conn->query("SELECT COALESCE(SUM(final_price),0) s FROM booking WHERE $cardWhere")->fetch_assoc()['s'];
  $k_gross_total = (float)$conn->query("SELECT COALESCE(SUM(total_price),0) s FROM booking WHERE $cardWhere")->fetch_assoc()['s'];
  $k_disc_total = (float)$conn->query("SELECT COALESCE(SUM(total_discount),0) s FROM booking WHERE $cardWhere")->fetch_assoc()['s'];
  $ym = date('Y-m');
  $k_net_mtd = (float)$conn->query("SELECT COALESCE(SUM(final_price),0) s FROM booking WHERE $cardWhere AND DATE_FORMAT(booking_date,'%Y-%m')='$ym'")->fetch_assoc()['s'];

  if($view === 'years'){
    $sql = "
      SELECT YEAR(booking_date) AS y,
             COALESCE(SUM(final_price),0)      AS net
      FROM booking
      WHERE status IN ($confirmedStatus,$completedStatus)
      GROUP BY YEAR(booking_date)
      ORDER BY YEAR(booking_date)
    ";
    $rs = $conn->query($sql);
    $keys=[]; $labels=[]; $netSeries=[];
    while($row=$rs->fetch_assoc()){
      $y = (int)$row['y'];
      if($y===0) continue;
      $keys[]=$y;
      $labels[]=(string)$y;
      $netSeries[]=round((float)$row['net'],2);
    }

    echo json_encode([
      'type'=>'years',
      'keys'=>$keys,
      'labels'=>$labels,
      'series'=>['net'=>$netSeries],
      'cards'=>[
        'net_total'=>$k_net_total,
        'net_mtd'=>$k_net_mtd,
        'gross_total'=>$k_gross_total,
        'disc_total'=>$k_disc_total
      ]
    ]);
    exit;
  }

  if($view === 'months'){
    if($year < $minYear || $year > $maxYear){ $year = (int)date('Y'); }
    $sql = "
      SELECT MONTH(booking_date) AS m,
             COALESCE(SUM(final_price),0) AS net
      FROM booking
      WHERE status IN ($confirmedStatus,$completedStatus) AND YEAR(booking_date)=$year
      GROUP BY MONTH(booking_date)
      ORDER BY MONTH(booking_date)
    ";
    $rs = $conn->query($sql);
    $map=[];
    while($row=$rs->fetch_assoc()){
      $map[(int)$row['m']] = round((float)$row['net'],2);
    }
    $keys=[]; $labels=[]; $netSeries=[];
    for($m=1;$m<=12;$m++){
      $keys[]=$m;
      $labels[]=str_pad((string)$m,2,'0',STR_PAD_LEFT);
      $netSeries[] = $map[$m] ?? 0;
    }

    echo json_encode([
      'type'=>'months',
      'year'=>$year,
      'keys'=>$keys,
      'labels'=>$labels,
      'series'=>['net'=>$netSeries],
      'cards'=>[
        'net_total'=>$k_net_total,
        'net_mtd'=>$k_net_mtd,
        'gross_total'=>$k_gross_total,
        'disc_total'=>$k_disc_total
      ]
    ]);
    exit;
  }

  if($view === 'month_table'){
    if($month < 1 || $month > 12) $month = (int)date('n');
    $stmt = $conn->prepare("SELECT
        b.booking_id,
        b.b_created_at,
        b.booking_date,
        b.time_start,
        b.time_end,
        b.total_price,
        b.total_discount,
        b.final_price,
        b.status,
        c.customer_name,
        s.staff_name,
        COALESCE(GROUP_CONCAT(DISTINCT sv.service_name ORDER BY sv.service_name SEPARATOR ', '), '') AS services
      FROM booking b
      LEFT JOIN customer c ON b.customer_id=c.customer_id
      LEFT JOIN staff s ON b.staff_id=s.staff_id
      LEFT JOIN booking_seviceop bs ON bs.booking_id=b.booking_id
      LEFT JOIN service_option so ON bs.option_id=so.option_id
      LEFT JOIN service sv ON so.service_id=sv.service_id
      WHERE b.status IN ($confirmedStatus,$completedStatus)
        AND YEAR(b.booking_date)=?
        AND MONTH(b.booking_date)=?
      GROUP BY b.booking_id
      ORDER BY b.booking_date ASC, b.time_start ASC");
    $stmt->bind_param('ii', $year, $month);
    $stmt->execute();
    $rs = $stmt->get_result();
    $rows=[];
    $gross=0; $disc=0; $net=0;
    while($row=$rs->fetch_assoc()){
      $bookedAt = $row['b_created_at'] ? date('Y-m-d H:i', strtotime($row['b_created_at'])) : '-';
      $serviceAt = $row['booking_date'] ? $row['booking_date'] : '';
      if($serviceAt !== ''){
        $start = $row['time_start'] ? substr($row['time_start'],0,5) : '';
        $end = $row['time_end'] ? substr($row['time_end'],0,5) : '';
        $serviceAt = trim($serviceAt.' '.($start!==''?$start:'').($end!==''?"-$end":''));
      }else{
        $serviceAt = '-';
      }
      $gross += (float)$row['total_price'];
      $disc  += (float)$row['total_discount'];
      $net   += (float)$row['final_price'];
      $statusCode = booking_status_code($row['status']);
      $rows[] = [
        'booking_id' => (int)$row['booking_id'],
        'booked_at' => esc($bookedAt),
        'service_at' => esc($serviceAt ?: '-'),
        'customer' => esc($row['customer_name'] ?: '-'),
        'services' => esc($row['services'] ?: '-'),
        'staff' => esc($row['staff_name'] ?: '-'),
        'gross' => round((float)$row['total_price'],2),
        'discount' => round((float)$row['total_discount'],2),
        'net' => round((float)$row['final_price'],2),
        'status_label' => esc(booking_status_label($statusCode)),
        'status_badge' => esc(booking_status_badge_class($statusCode))
      ];
    }
    $stmt->close();

    echo json_encode([
      'type'=>'month_table',
      'year'=>$year,
      'month'=>$month,
      'label'=>date('F', mktime(0,0,0,$month,1,$year)),
      'rows'=>$rows,
      'totals'=>[
        'gross'=>round($gross,2),
        'discount'=>round($disc,2),
        'net'=>round($net,2)
      ],
      'cards'=>[
        'net_total'=>$k_net_total,
        'net_mtd'=>$k_net_mtd,
        'gross_total'=>$k_gross_total,
        'disc_total'=>$k_disc_total
      ]
    ]);
    exit;
  }
}

// ======================= TABLE: filters + sort + pagination =======================
$statusParam = $_GET['status'] ?? 'all';
$statusFilter = null;
if($statusParam !== 'all'){
  $parsedStatus = booking_status_code($statusParam);
  if(in_array($parsedStatus, [$confirmedStatus, $completedStatus], true)){
    $statusFilter = $parsedStatus;
  }
}
$statusValue = $statusFilter === null ? 'all' : (string)$statusFilter;
$serviceF = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$sort   = $_GET['sort'] ?? 'booked_at';
$dir    = strtoupper($_GET['dir'] ?? 'DESC'); $dir=$dir==='ASC'?'ASC':'DESC';

$rangeType = $_GET['range_type'] ?? 'all';
if(!in_array($rangeType, ['all','day','month','year'], true)) $rangeType = 'all';
$rangeDay = $_GET['range_day'] ?? '';
$rangeMonth = isset($_GET['range_month']) ? (int)$_GET['range_month'] : (int)date('n');
if($rangeMonth < 1 || $rangeMonth > 12) $rangeMonth = (int)date('n');
$rangeYear = isset($_GET['range_year']) ? (int)$_GET['range_year'] : (int)date('Y');

$sortMap=[
  'booked_at'  => "b.b_created_at $dir",
  'service_at' => "b.booking_date $dir, b.time_start $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['booked_at'];

// WHERE
$where = ["b.status IN ($confirmedStatus,$completedStatus)"]; $types=""; $params=[];
if($statusFilter !== null){
  $where[]="b.status=?"; $params[]=$statusFilter; $types.="i";
}
if($serviceF>0){
  $where[]="EXISTS (SELECT 1 FROM booking_seviceop bs JOIN service_option so ON bs.option_id=so.option_id WHERE bs.booking_id=b.booking_id AND so.service_id=?)";
  $params[]=$serviceF; $types.="i";
}
if($rangeType==='day'){
  if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$rangeDay)){
    $where[]="b.booking_date=?"; $params[]=$rangeDay; $types.='s';
  }
}elseif($rangeType==='month'){
  $where[]="YEAR(b.booking_date)=?"; $params[]=$rangeYear; $types.='i';
  $where[]="MONTH(b.booking_date)=?"; $params[]=$rangeMonth; $types.='i';
}elseif($rangeType==='year'){
  $where[]="YEAR(b.booking_date)=?"; $params[]=$rangeYear; $types.='i';
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

$per = 20;
$pages = max(1, (int)ceil($total / $per));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$off = ($page - 1) * $per;

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

// List
$sqlList="
  SELECT
    b.booking_id,
    b.b_created_at,
    b.booking_date,
    b.time_start,
    b.time_end,
    b.total_price,
    b.total_discount,
    b.final_price,
    b.status,
    c.customer_name,
    c.gmail,
    s.staff_name,
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
            <label class="form-label small-label">สถานะ</label>
            <select class="form-select" name="status">
              <option value="all" <?= $statusValue==='all'?'selected':'' ?>>ทั้งหมด</option>
              <option value="<?=$confirmedStatus?>" <?= $statusValue===(string)$confirmedStatus?'selected':'' ?>>Confirmed</option>
              <option value="<?=$completedStatus?>" <?= $statusValue===(string)$completedStatus?'selected':'' ?>>Completed</option>
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
            <label class="form-label small-label">ช่วงเวลา</label>
            <select class="form-select" name="range_type" id="rangeType">
              <option value="all" <?= $rangeType==='all'?'selected':'' ?>>ทั้งหมด</option>
              <option value="day" <?= $rangeType==='day'?'selected':'' ?>>รายวัน</option>
              <option value="month" <?= $rangeType==='month'?'selected':'' ?>>รายเดือน</option>
              <option value="year" <?= $rangeType==='year'?'selected':'' ?>>รายปี</option>
            </select>
          </div>
          <div class="col-md-auto range-extra <?= $rangeType==='day'?'':'d-none' ?>" data-range="day">
            <label class="form-label small-label">เลือกวันที่</label>
            <input type="date" class="form-control" name="range_day" value="<?=esc($rangeDay)?>" <?= $rangeType==='day'?'':'disabled' ?>>
          </div>
          <div class="col-md-auto range-extra <?= $rangeType==='month'?'':'d-none' ?>" data-range="month">
            <label class="form-label small-label">เลือกเดือน/ปี</label>
            <div class="d-flex gap-2">
              <select class="form-select" name="range_month" <?= $rangeType==='month'?'':'disabled' ?>>
                <?php for($m=1;$m<=12;$m++): ?>
                  <option value="<?=$m?>" <?= $rangeMonth==$m?'selected':'' ?>><?=$m?></option>
                <?php endfor; ?>
              </select>
              <input type="number" class="form-control" name="range_year" value="<?=esc($rangeYear)?>" min="2000" max="2100" <?= $rangeType==='month'?'':'disabled' ?>>
            </div>
          </div>
          <div class="col-md-auto range-extra <?= $rangeType==='year'?'':'d-none' ?>" data-range="year">
            <label class="form-label small-label">เลือกปี</label>
            <input type="number" class="form-control" name="range_year" value="<?=esc($rangeYear)?>" min="2000" max="2100" <?= $rangeType==='year'?'':'disabled' ?>>
          </div>
          <div class="col-md-auto">
            <label class="form-label small-label">Sort by</label>
            <div class="input-group">
              <select class="form-select" name="sort">
                <option value="booked_at"  <?= $sort==='booked_at'?'selected':'' ?>>วันเวลาที่ทำการจอง</option>
                <option value="service_at" <?= $sort==='service_at'?'selected':'' ?>>วันเวลาที่เข้าใช้บริการ</option>
              </select>
              <select class="form-select" name="dir">
                <option value="ASC"  <?= $dir==='ASC'?'selected':'' ?>>เก่า → ใหม่</option>
                <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>ใหม่ → เก่า</option>
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
                <th>ID</th>
                <th>วันเวลาที่ทำการจอง</th>
                <th>วันเวลาที่เข้าใช้บริการ</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Staff</th>
                <th class="text-end">ราคาก่อนหักส่วนลด</th>
                <th class="text-end">ส่วนลด</th>
                <th class="text-end">ราคาหลังหักส่วนลด</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="10" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $r): ?>
                <tr>
                  <?php
                    $bookedAt = $r['b_created_at'] ? date('Y-m-d H:i', strtotime($r['b_created_at'])) : '-';
                    $serviceDate = $r['booking_date'] ?: '';
                    $startTime = $r['time_start'] ? substr($r['time_start'],0,5) : '';
                    $endTime = $r['time_end'] ? substr($r['time_end'],0,5) : '';
                    $serviceAt = '-';
                    if($serviceDate !== ''){
                      $timeRange = trim($startTime.($endTime!==''?"-$endTime":''));
                      $serviceAt = trim($serviceDate.' '.$timeRange);
                    }
                    $statusCode = booking_status_code($r['status']);
                    $badgeClass = booking_status_badge_class($statusCode);
                    $statusLabel = booking_status_label($statusCode);
                  ?>
                  <td><?=esc($r['booking_id'])?></td>
                  <td><?=esc($bookedAt)?></td>
                  <td><?=esc($serviceAt)?></td>
                  <td><?=esc($r['customer_name']?:'N/A')?><?php if(!empty($r['gmail'])): ?><br><small class="text-muted"><?=esc($r['gmail'])?></small><?php endif; ?></td>
                  <td><?=esc($r['services']?:'-')?></td>
                  <td><?=esc($r['staff_name']?:'-')?></td>
                  <td class="text-end"><?=number_format((float)$r['total_price'],2)?></td>
                  <td class="text-end text-danger">-<?=number_format((float)$r['total_discount'],2)?></td>
                  <td class="text-end fw-bold text-success"><?=number_format((float)$r['final_price'],2)?></td>
                  <td><span class="badge <?=$badgeClass?>"><?=esc($statusLabel)?></span></td>
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
                <th></th>
              </tr>
            </tfoot>
            <?php endif; ?>
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="mb-0" id="chartTitle">รายได้รายปี</h5>
          <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="chartBack"><i class="bi bi-arrow-left"></i> ย้อนกลับ</button>
        </div>
        <div class="text-muted mt-1">ข้อมูลเฉพาะการจองที่ยืนยันแล้วและเสร็จสิ้น</div>

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
        <div class="text-muted mt-2">คลิกแท่งปีเพื่อดูรายเดือน และคลิกแท่งเดือนเพื่อดูตารางของเดือนนั้น</div>

        <div id="monthTable" class="mt-4"></div>

      </div></div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const rangeSelect=document.getElementById('rangeType');
function updateRangeControls(){
  const value=rangeSelect?.value||'all';
  document.querySelectorAll('.range-extra').forEach(el=>{
    const target=el.getAttribute('data-range');
    const active=value===target;
    el.classList.toggle('d-none',!active);
    el.querySelectorAll('input,select').forEach(ctrl=>{ ctrl.disabled=!active; });
  });
}
if(rangeSelect){
  rangeSelect.addEventListener('change',updateRangeControls);
  updateRangeControls();
}

const chartCanvas=document.getElementById('incomeChart');
const chartCtx=chartCanvas?chartCanvas.getContext('2d'):null;
const chartTitleEl=document.getElementById('chartTitle');
const chartBackBtn=document.getElementById('chartBack');
const monthTableWrap=document.getElementById('monthTable');
let chart;
let currentYear=null;

const nf=(v)=>Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});

function updateCards(cards){
  if(!cards) return;
  const mapping={
    k_net_total:cards.net_total,
    k_net_mtd:cards.net_mtd,
    k_gross_total:cards.gross_total,
    k_disc_total:cards.disc_total
  };
  Object.entries(mapping).forEach(([id,val])=>{
    const el=document.getElementById(id);
    if(el) el.textContent=nf(val);
  });
}

async function loadChart(view='years', year=null){
  if(!chartCtx) return;
  try{
    const url=new URL(location.href);
    url.search='';
    url.searchParams.set('action','stats');
    url.searchParams.set('view',view);
    if(view==='months'){
      const targetYear=year ?? currentYear ?? new Date().getFullYear();
      currentYear=targetYear;
      url.searchParams.set('year',targetYear);
    }else{
      currentYear=null;
    }
    const res=await fetch(url.toString(),{cache:'no-store'});
    const data=await res.json();
    updateCards(data.cards || null);
    renderChart(data);
    if(view==='years' && monthTableWrap){
      monthTableWrap.innerHTML='';
    }
  }catch(err){
    console.error(err);
  }
}

function renderChart(data){
  if(!chartCtx) return;
  const labels=data.labels || [];
  const netSeries=data.series?.net || [];
  const keys=data.keys || [];
  if(chart) chart.destroy();
  chart=new Chart(chartCtx,{
    type:'bar',
    data:{
      labels,
      datasets:[{
        label:'รายได้หลังหักส่วนลด',
        data:netSeries,
        backgroundColor:'rgba(13,110,253,0.7)',
        borderColor:'rgba(13,110,253,1)',
        borderWidth:1,
        borderRadius:6
      }]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        y:{
          beginAtZero:true,
          ticks:{ callback:value=>nf(value) }
        }
      },
      plugins:{
        legend:{display:false},
        tooltip:{ callbacks:{ label:ctx=>`รายได้: ฿${nf(ctx.parsed.y)}` } }
      },
      onClick:(evt,elements)=>{
        if(!elements.length) return;
        const idx=elements[0].index;
        const key=keys[idx];
        if(data.type==='years' && key){
          if(monthTableWrap) monthTableWrap.innerHTML='';
          loadChart('months',key);
        }else if(data.type==='months' && key){
          loadMonthTable(data.year,key);
        }
      }
    }
  });

  if(chartTitleEl){
    if(data.type==='months'){
      chartTitleEl.textContent=`รายได้ปี ${data.year}`;
      chartBackBtn?.classList.remove('d-none');
    }else{
      chartTitleEl.textContent='รายได้รายปี';
      chartBackBtn?.classList.add('d-none');
    }
  }
}

async function loadMonthTable(year, month){
  if(!monthTableWrap) return;
  monthTableWrap.innerHTML='<div class="text-muted">กำลังโหลด...</div>';
  try{
    const url=new URL(location.href);
    url.search='';
    url.searchParams.set('action','stats');
    url.searchParams.set('view','month_table');
    url.searchParams.set('year',year);
    url.searchParams.set('month',month);
    const res=await fetch(url.toString(),{cache:'no-store'});
    const data=await res.json();
    updateCards(data.cards || null);
    const rows=Array.isArray(data.rows)?data.rows:[];
    if(rows.length===0){
      monthTableWrap.innerHTML='<div class="alert alert-info mb-0">ไม่พบข้อมูลในเดือนนี้</div>';
      return;
    }
    const fmt=new Intl.NumberFormat(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    let html='<div class="card"><div class="card-body">';
    html+=`<h5 class="card-title mb-3">รายการเดือน ${data.label ?? month} ${data.year}</h5>`;
    html+='<div class="table-responsive"><table class="table table-striped table-hover align-middle">';
    html+='<thead class="table-light"><tr><th>ID</th><th>วันเวลาที่ทำการจอง</th><th>วันเวลาที่เข้าใช้บริการ</th><th>Customer</th><th>Service</th><th>Staff</th><th class="text-end">ราคาก่อนหักส่วนลด</th><th class="text-end">ส่วนลด</th><th class="text-end">ราคาหลังหักส่วนลด</th><th>Status</th></tr></thead><tbody>';
    rows.forEach(row=>{
      html+=`<tr><td>${row.booking_id}</td><td>${row.booked_at}</td><td>${row.service_at}</td><td>${row.customer}</td><td>${row.services}</td><td>${row.staff}</td><td class="text-end">${fmt.format(row.gross)}</td><td class="text-end text-danger">-${fmt.format(row.discount)}</td><td class="text-end text-success">${fmt.format(row.net)}</td><td><span class="badge ${row.status_badge}">${row.status_label}</span></td></tr>`;
    });
    const totals=data.totals || {gross:0,discount:0,net:0};
    html+='</tbody><tfoot><tr class="table-light"><th colspan="6" class="text-end">รวม</th>';
    html+=`<th class="text-end">${fmt.format(totals.gross || 0)}</th><th class="text-end text-danger">-${fmt.format(totals.discount || 0)}</th><th class="text-end text-success">${fmt.format(totals.net || 0)}</th><th></th></tr></tfoot>`;
    html+='</table></div></div></div>';
    monthTableWrap.innerHTML=html;
  }catch(err){
    console.error(err);
    monthTableWrap.innerHTML='<div class="alert alert-danger mb-0">ไม่สามารถโหลดข้อมูลได้</div>';
  }
}

chartBackBtn?.addEventListener('click',()=>{
  if(monthTableWrap) monthTableWrap.innerHTML='';
  loadChart('years');
});

document.getElementById('chart-tab')?.addEventListener('shown.bs.tab',()=>{
  if(!chart){
    loadChart('years');
  }
});
</script>
</body>
</html>
