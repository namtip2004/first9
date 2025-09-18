<?php
// report_customer.php — Full page (Table upgraded to show aggregates like sample; Chart 3 lines with legend; KPI cards not filtered)
require_once("connect_db.php");

function safe($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// ---------- Year range meta (for chart pickers) ----------
$yearMeta = $conn->query("SELECT MIN(YEAR(c_created_at)) AS miny, MAX(YEAR(c_created_at)) AS maxy FROM customer")->fetch_assoc();
$minYear = (int)($yearMeta['miny'] ?? date('Y'));
$maxYear = (int)($yearMeta['maxy'] ?? date('Y'));
if ($minYear === 0) { $minYear = (int)date('Y'); $maxYear = (int)date('Y'); }

// ========================= AJAX: stats for cards + chart =========================
if (isset($_GET['action']) && $_GET['action']==='stats') {
  header('Content-Type: application/json; charset=utf-8');

  $period = $_GET['period'] ?? 'month';   // all|month|year
  $series = $_GET['series'] ?? 'new';     // new|total

  // Pickers
  $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
  $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
  $start_year = isset($_GET['start_year']) ? (int)$_GET['start_year'] : $minYear;
  $end_year   = isset($_GET['end_year'])   ? (int)$_GET['end_year']   : $maxYear;
  if ($start_year > $end_year) { $t=$start_year; $start_year=$end_year; $end_year=$t; }

  // ====== KPI CARDS (GLOBAL; NOT FILTERED) ======
  $total_all  = (int)$conn->query("SELECT COUNT(*) AS c FROM customer")->fetch_assoc()['c'];
  $active_all = (int)$conn->query("SELECT COUNT(*) AS c FROM customer WHERE LOWER(COALESCE(account_status,''))='active'")->fetch_assoc()['c'];
  $ym = date('Y-m');
  $new_this_month = (int)$conn->query("SELECT COUNT(*) AS c FROM customer WHERE DATE_FORMAT(c_created_at,'%Y-%m')='$ym'")->fetch_assoc()['c'];

  // ====== CHART ======
  $make_cumulative = function(array $labels, array $mapCounts, int $baseBefore) {
    $data=[]; $run=$baseBefore;
    foreach ($labels as $b) {
      $run += ($mapCounts[$b] ?? 0);
      $data[] = $run;
    }
    return $data;
  };

  $labels=[]; $bucketExpr=""; $whereRange=""; $baseCutoff=""; $axis='';
  if ($period==='month') {
    $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($d=1;$d<=$days;$d++) $labels[] = str_pad((string)$d,2,'0',STR_PAD_LEFT);
    $bucketExpr = "DATE_FORMAT(c_created_at,'%d')";
    $whereRange = "YEAR(c_created_at)=$year AND MONTH(c_created_at)=$month";
    $baseCutoff = sprintf('%04d-%02d-01', $year, $month);
    $axis = 'วัน';
  } elseif ($period==='year') {
    $labels = ['01','02','03','04','05','06','07','08','09','10','11','12'];
    $bucketExpr = "DATE_FORMAT(c_created_at,'%m')";
    $whereRange = "YEAR(c_created_at)=$year";
    $baseCutoff = sprintf('%04d-01-01', $year);
    $axis = 'เดือน';
  } else { // all
    for ($y=$start_year; $y<=$end_year; $y++) $labels[]=(string)$y;
    $bucketExpr = "YEAR(c_created_at)";
    $whereRange = "YEAR(c_created_at) BETWEEN $start_year AND $end_year";
    $baseCutoff = sprintf('%04d-01-01', $start_year);
    $axis = 'ปี';
  }

  // counts per bucket by gender
  $sql = "
    SELECT $bucketExpr AS b, LOWER(COALESCE(gender,'')) AS g, COUNT(*) AS cnt
    FROM customer
    WHERE $whereRange
    GROUP BY b, g
    ORDER BY b
  ";
  $res = $conn->query($sql);
  $map_all=[]; $map_male=[]; $map_female=[];
  while ($row = $res->fetch_assoc()) {
    $b=(string)$row['b']; $g=$row['g']; $c=(int)$row['cnt'];
    $map_all[$b] = ($map_all[$b] ?? 0) + $c;
    if ($g==='male')   $map_male[$b]   = ($map_male[$b]   ?? 0) + $c;
    if ($g==='female') $map_female[$b] = ($map_female[$b] ?? 0) + $c;
  }

  if ($series==='new') {
    $data_all=[]; $data_male=[]; $data_female=[];
    foreach ($labels as $b) {
      $data_all[]    = $map_all[$b]    ?? 0;
      $data_male[]   = $map_male[$b]   ?? 0;
      $data_female[] = $map_female[$b] ?? 0;
    }
  } else {
    $base_all    = (int)$conn->query("SELECT COUNT(*) AS c FROM customer WHERE DATE(c_created_at)<'$baseCutoff'")->fetch_assoc()['c'];
    $base_male   = (int)$conn->query("SELECT COUNT(*) AS c FROM customer WHERE DATE(c_created_at)<'$baseCutoff' AND LOWER(COALESCE(gender,''))='male'")->fetch_assoc()['c'];
    $base_female = (int)$conn->query("SELECT COUNT(*) AS c FROM customer WHERE DATE(c_created_at)<'$baseCutoff' AND LOWER(COALESCE(gender,''))='female'")->fetch_assoc()['c'];
    $data_all    = $make_cumulative($labels, $map_all,    $base_all);
    $data_male   = $make_cumulative($labels, $map_male,   $base_male);
    $data_female = $make_cumulative($labels, $map_female, $base_female);
  }

  echo json_encode([
    'cards'=>[
      'total_all'      => $total_all,
      'active_all'     => $active_all,
      'new_this_month' => $new_this_month
    ],
    'chart'=>[
      'labels'=>$labels,
      'series'=>[
        'all'    => $data_all,
        'male'   => $data_male,
        'female' => $data_female
      ],
      'axis'=>$axis
    ],
    'meta'=>[ 'minYear'=>$minYear, 'maxYear'=>$maxYear ]
  ]);
  exit;
}

// ========================= TABLE (aggregate per customer) =========================
$search      = trim($_GET['q'] ?? '');
$status      = $_GET['status'] ?? 'all';      // active/inactive/all
$genderTable = $_GET['gender'] ?? 'all';      // male/female/other/all
$sort        = $_GET['sort'] ?? 'c_created_at';
$dir         = strtoupper($_GET['dir'] ?? 'DESC');
$dir         = ($dir==='ASC') ? 'ASC' : 'DESC';

$sortMap = [
  'c_created_at'      => "c.c_created_at $dir",
  'customer_name'     => "c.customer_name $dir",
  'total_bookings'    => "total_bookings $dir",
  'total_spent'       => "total_spent $dir",
  'avg_booking_value' => "avg_booking_value $dir",
  'last_booking'      => "last_booking $dir"
];
$orderBy = $sortMap[$sort] ?? $sortMap['c_created_at'];

$whereList = "1=1";
$params    = []; 
$types     = "";

if ($search !== '') {
  $whereList .= " AND (c.customer_name LIKE ? OR c.gmail LIKE ?)";
  $kw = "%$search%";
  $params[] = $kw; $params[] = $kw; $types .= "ss";
}
if ($status !== 'all') {
  $whereList .= " AND LOWER(COALESCE(c.account_status,'')) = LOWER(?)";
  $params[] = $status; $types .= "s";
}
if ($genderTable !== 'all') {
  $whereList .= " AND LOWER(COALESCE(c.gender,'')) = LOWER(?)";
  $params[] = $genderTable; $types .= "s";
}

// Count after filters
$sqlCount = "SELECT COUNT(*) AS cnt FROM customer c WHERE $whereList";
$stmt = $conn->prepare($sqlCount);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRows = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Pagination
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page-1)*$perPage;

// Data with aggregates
$sqlList = "
  SELECT
    c.customer_id, c.customer_name, c.gender, c.gmail, c.tel,
    c.account_status, c.c_created_at, c.profileimg, c.birthday,
    COUNT(b.booking_id)                           AS total_bookings,
    COALESCE(SUM(b.final_price), 0)               AS total_spent,
    MAX(COALESCE(b.b_created_at, b.booking_date)) AS last_booking,
    AVG(b.final_price)                            AS avg_booking_value
  FROM customer c
  LEFT JOIN booking b ON b.customer_id = c.customer_id
  WHERE $whereList
  GROUP BY c.customer_id
  ORDER BY $orderBy
  LIMIT ? OFFSET ?
";
$params2 = $params; $types2 = $types . "ii";
$params2[] = $perPage; $params2[] = $offset;

$stmt = $conn->prepare($sqlList);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$rs = $stmt->get_result();
$rows = [];
while ($r = $rs->fetch_assoc()) $rows[] = $r;
$stmt->close();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Customer Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <!-- Custom -->
  <link rel="stylesheet" href="style.css">
  <style>
    .tab-toolbar{gap:.5rem;align-items:center}
    .card-kpi .card-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center}
    .kpi-value{font-size:28px;font-weight:700;margin:0}
    .kpi-label{color:#6c757d;margin:0}
    .small-label{font-size:.9rem;color:#6c757d}
    .legend-note{font-size:.85rem;color:#6c757d}
  </style>
</head>
<body>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main">

  <div class="pagetitle">
    <h1>รายงานลูกค้า (Customer Report)</h1>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs nav-tabs-bordered" id="custTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#tab-table" type="button" role="tab">ตาราง</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#tab-chart" type="button" role="tab">กราฟ</button>
    </li>
  </ul>

  <div class="tab-content" id="custTabsContent">
    <!-- TAB: TABLE (AGGREGATED) -->
    <div class="tab-pane fade show active" id="tab-table" role="tabpanel" aria-labelledby="table-tab">
      <div class="card mt-3">
        <div class="card-body">
          <div class="d-flex flex-wrap tab-toolbar mb-3">
            <form class="row g-2 align-items-end" method="get">
              <input type="hidden" name="tab" value="table">
              <div class="col-auto">
                <label class="form-label small-label">ค้นหา</label>
                <input type="text" class="form-control" name="q" value="<?= safe($search) ?>" placeholder="ชื่อ/อีเมล">
              </div>
              <div class="col-auto">
                <label class="form-label small-label">สถานะ</label>
                <select class="form-select" name="status">
                  <option value="all"      <?= $status==='all'?'selected':'' ?>>ทั้งหมด</option>
                  <option value="active"   <?= $status==='active'?'selected':'' ?>>Active</option>
                  <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
              </div>
              <div class="col-auto">
                <label class="form-label small-label">เพศ</label>
                <select class="form-select" name="gender">
                  <option value="all"    <?= $genderTable==='all'?'selected':'' ?>>ทั้งหมด</option>
                  <option value="male"   <?= $genderTable==='male'?'selected':'' ?>>male</option>
                  <option value="female" <?= $genderTable==='female'?'selected':'' ?>>female</option>
                  <option value="other"  <?= $genderTable==='other'?'selected':'' ?>>other/ไม่ระบุ</option>
                </select>
              </div>
              <div class="col-auto">
                <label class="form-label small-label">Sort by</label>
                <div class="input-group">
                  <select class="form-select" name="sort">
                    <option value="c_created_at"      <?= $sort==='c_created_at'?'selected':'' ?>>วันที่สมัคร</option>
                    <option value="customer_name"     <?= $sort==='customer_name'?'selected':'' ?>>ชื่อลูกค้า</option>
                    <option value="total_bookings"    <?= $sort==='total_bookings'?'selected':'' ?>>จำนวนครั้งที่จอง</option>
                    <option value="total_spent"       <?= $sort==='total_spent'?'selected':'' ?>>ยอดใช้จ่ายรวม</option>
                    <option value="avg_booking_value" <?= $sort==='avg_booking_value'?'selected':'' ?>>ค่าเฉลี่ยต่อบิล</option>
                    <option value="last_booking"      <?= $sort==='last_booking'?'selected':'' ?>>จองล่าสุด</option>
                  </select>
                  <select class="form-select" name="dir">
                    <option value="ASC"  <?= $dir==='ASC'?'selected':'' ?>>ASC</option>
                    <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>>DESC</option>
                  </select>
                </div>
              </div>
              <div class="col-auto">
                <button class="btn btn-primary"><i class="bi bi-search"></i> ใช้ตัวกรอง</button>
              </div>
            </form>
            <div class="ms-auto">
              <span class="badge bg-primary">จำนวนทั้งหมด: <?= number_format($totalRows) ?> ราย</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="customerTable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Profile</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Gender</th>
                  <th>Age</th>
                  <th>Status</th>
                  <th class="text-end">Total Bookings</th>
                  <th class="text-end">Total Spent</th>
                  <th class="text-end">Avg. Booking</th>
                  <th>Last Booking</th>
                  <th>Member Since</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php if(empty($rows)): ?>
                <tr><td colspan="14" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
              <?php else: foreach($rows as $i=>$r):
                // Age
                $ageText = 'N/A';
                if (!empty($r['birthday'])) {
                  try { $age = (new DateTime())->diff(new DateTime($r['birthday']))->y; $ageText = $age.' years'; } catch (Exception $e) {}
                }
                $g = strtolower((string)$r['gender']);
                $genderLabel = ucfirst($g ?: 'other');
                $isActive = strtolower((string)$r['account_status'])==='active';
                $lastBk   = $r['last_booking'] ? date('M d, Y', strtotime($r['last_booking'])) : null;
                $memberSince = $r['c_created_at'] ? date('M d, Y', strtotime($r['c_created_at'])) : null;
              ?>
                <tr>
                  <td><?= $offset + $i + 1 ?></td>
                  <td>
                    <?php if (!empty($r['profileimg'])): ?>
                      <img src="assets/img/<?= safe($r['profileimg']) ?>" alt="Profile"
                           style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                      <div style="width:40px;height:40px;background:#f0f0f0;border-radius:50%;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-person"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?= safe($r['customer_name']) ?></td>
                  <td><?= safe($r['gmail']) ?></td>
                  <td><?= safe($r['tel']) ?></td>
                  <td><?= safe($genderLabel) ?></td>
                  <td><?= $ageText ?></td>
                  <td>
                    <span class="badge bg-<?= $isActive?'success':'danger' ?>">
                      <?= $isActive ? 'Active' : 'Inactive' ?>
                    </span>
                  </td>
                  <td class="text-end"><span class="badge bg-primary"><?= number_format((int)$r['total_bookings']) ?></span></td>
                  <td class="text-end text-success fw-bold">฿<?= number_format((float)$r['total_spent'], 2) ?></td>
                  <td class="text-end">
                    <?php if ($r['avg_booking_value'] !== null): ?>
                      ฿<?= number_format((float)$r['avg_booking_value'], 2) ?>
                    <?php else: ?>
                      <span class="text-muted">No bookings</span>
                    <?php endif; ?>
                  </td>
                  <td><?= $lastBk ? '<small class="text-muted">'.$lastBk.'</small>' : '<span class="text-muted">Never</span>' ?></td>
                  <td><small class="text-muted"><?= $memberSince ?: '-' ?></small></td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="customer_detail.php?id=<?= (int)$r['customer_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="customer_bookings.php?id=<?= (int)$r['customer_id'] ?>" class="btn btn-sm btn-outline-success" title="View Bookings">
                        <i class="bi bi-calendar-check"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

    <!-- TAB: CHART -->
    <div class="tab-pane fade" id="tab-chart" role="tabpanel" aria-labelledby="chart-tab">
      <div class="card mt-3">
        <div class="card-body">
          <!-- Filters (legend toggles gender lines; keep series selector) -->
          <div class="row g-3 align-items-end">
            <div class="col-12 col-md-auto">
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
            <div class="col-12 col-md-auto">
              <label class="form-label small-label">ซี่รี่ส์ข้อมูล</label>
              <select id="series" class="form-select">
                <option value="new" selected>สมัครใหม่</option>
                <option value="total">ทั้งหมด (สะสม)</option>
              </select>
            </div>

            <!-- Period-specific controls -->
            <div class="col-12 col-md-auto period-control" id="ctl-month">
              <label class="form-label small-label">เดือน/ปี</label>
              <div class="d-flex gap-2">
                <select id="month" class="form-select">
                  <?php for($m=1;$m<=12;$m++): ?>
                  <option value="<?= $m ?>" <?= $m==(int)date('n')?'selected':'' ?>><?= $m ?></option>
                  <?php endfor; ?>
                </select>
                <select id="year_m" class="form-select">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?= $y ?>" <?= $y==(int)date('Y')?'selected':'' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <!-- <div class="form-text">แสดงรายวันของเดือนที่เลือก</div> -->
            </div>

            <div class="col-12 col-md-auto period-control d-none" id="ctl-year">
              <label class="form-label small-label">ปี</label>
              <select id="year_y" class="form-select">
                <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                <option value="<?= $y ?>" <?= $y==(int)date('Y')?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
              <!-- <div class="form-text">แสดงรายเดือนของปีที่เลือก</div> -->
            </div>

            <div class="col-12 col-md-auto period-control d-none" id="ctl-all">
              <label class="form-label small-label">ช่วงปี</label>
              <div class="d-flex gap-2">
                <select id="start_year" class="form-select">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?= $y ?>"><?= $y ?></option>
                  <?php endfor; ?>
                </select>
                <select id="end_year" class="form-select">
                  <?php for($y=$minYear;$y<=$maxYear;$y++): ?>
                  <option value="<?= $y ?>" <?= $y==$maxYear?'selected':'' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <!-- <div class="form-text">แสดงรายปีตามช่วงที่เลือก</div> -->
            </div>

            <div class="col-12 col-md-auto">
              <button id="applyFilters" class="btn btn-primary"><i class="bi bi-filter"></i> ใช้ตัวกรอง</button>
            </div>
          </div>

          <!-- KPI Cards (not filtered) -->
          <div class="row mt-3">
            <div class="col-md-4">
              <div class="card card-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                  <div class="card-icon bg-primary-light"><i class="bi bi-people fs-3 text-primary"></i></div>
                  <div>
                    <p class="kpi-value" id="kpi_total">0</p>
                    <p class="kpi-label">จำนวนรวมทั้งหมด</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                  <div class="card-icon bg-success-light"><i class="bi bi-person-check fs-3 text-success"></i></div>
                  <div>
                    <p class="kpi-value" id="kpi_active">0</p>
                    <p class="kpi-label">เฉพาะ Active</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                  <div class="card-icon bg-warning-light"><i class="bi bi-person-plus fs-3"></i></div>
                  <div>
                    <p class="kpi-value" id="kpi_new">0</p>
                    <p class="kpi-label">New เดือนนี้</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- CHART -->
          <div class="mt-3" style="min-height:360px">
            <canvas id="custLineChart" height="120"></canvas>
          </div>
          <!-- <div class="legend-note mt-2">* คลิกสี่เหลี่ยมสีใน Legend เพื่อเปิด–ปิดเส้น (ทั้งหมด/ชาย/หญิง)</div> -->

        </div>
      </div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updatePeriodControls(){
  const p = document.querySelector('input[name="period"]:checked')?.value || 'month';
  document.querySelectorAll('.period-control').forEach(el=>el.classList.add('d-none'));
  if (p==='month') document.getElementById('ctl-month')?.classList.remove('d-none');
  if (p==='year')  document.getElementById('ctl-year')?.classList.remove('d-none');
  if (p==='all')   document.getElementById('ctl-all')?.classList.remove('d-none');
}
['p_all','p_month','p_year'].forEach(id=>document.getElementById(id).addEventListener('change', updatePeriodControls));
updatePeriodControls();

let chart;
async function loadStats(){
  const period = document.querySelector('input[name="period"]:checked')?.value || 'month';
  const series = document.getElementById('series').value;

  const url = new URL(window.location.href);
  url.search = '';
  url.searchParams.set('action','stats');
  url.searchParams.set('period',period);
  url.searchParams.set('series',series);

  if (period==='month'){
    url.searchParams.set('year',  document.getElementById('year_m').value);
    url.searchParams.set('month', document.getElementById('month').value);
  } else if (period==='year'){
    url.searchParams.set('year',  document.getElementById('year_y').value);
  } else {
    url.searchParams.set('start_year', document.getElementById('start_year').value);
    url.searchParams.set('end_year',   document.getElementById('end_year').value);
  }

  const res = await fetch(url.toString(), {cache:'no-store'});
  const data = await res.json();

  // KPIs
  document.getElementById('kpi_total').textContent  = (data.cards.total_all ?? 0).toLocaleString();
  document.getElementById('kpi_active').textContent = (data.cards.active_all ?? 0).toLocaleString();
  document.getElementById('kpi_new').textContent    = (data.cards.new_this_month ?? 0).toLocaleString();

  // Chart 3 lines: All / Male / Female
  const labels = data.chart.labels;
  const seriesAll    = data.chart.series.all    || [];
  const seriesMale   = data.chart.series.male   || [];
  const seriesFemale = data.chart.series.female || [];

  if (chart) chart.destroy();
  const ctx = document.getElementById('custLineChart').getContext('2d');
  chart = new Chart(ctx, {
    type:'line',
    data:{
      labels,
      datasets:[
        { label:'ทั้งหมด', data:seriesAll,    tension:.3, pointRadius:3, borderWidth:2 },
        { label:'ชาย',     data:seriesMale,   tension:.3, pointRadius:3, borderWidth:2 },
        { label:'หญิง',    data:seriesFemale, tension:.3, pointRadius:3, borderWidth:2 }
      ]
    },
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{
        x:{ title:{ display:true, text: data.chart.axis || '' } },
        y:{ beginAtZero:true, ticks:{ precision:0 } }
      },
      plugins:{
        legend:{ display:true }, // toggle via legend
        tooltip:{ mode:'index', intersect:false }
      }
    }
  });
}

document.getElementById('applyFilters').addEventListener('click', loadStats);
document.getElementById('chart-tab').addEventListener('shown.bs.tab', () => loadStats());
</script>
</body>
</html>