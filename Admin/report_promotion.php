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
$action = $_GET['action'] ?? null;

// ---------------------------------------------------------------
// AJAX handler for chart + KPI data
// ---------------------------------------------------------------
if ($action === 'stats') {
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
        $axisLabel = 'Day';
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
        $axisLabel = 'Month';
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
        $axisLabel = 'Year';
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

if ($action === 'promotion_services') {
    header('Content-Type: application/json; charset=utf-8');

    $promotionId = isset($_GET['promotion_id']) ? (int)$_GET['promotion_id'] : 0;
    if ($promotionId <= 0) {
        echo json_encode(['error' => 'invalid_promotion']);
        exit;
    }

    $promoStmt = $conn->prepare('SELECT pm_name FROM promotion WHERE promotion_id = ?');
    if ($promoStmt === false) {
        echo json_encode(['error' => 'cannot_prepare']);
        exit;
    }
    $promoStmt->bind_param('i', $promotionId);
    $promoStmt->execute();
    $promoRes = $promoStmt->get_result();
    $promotion = $promoRes->fetch_assoc();
    $promoStmt->close();

    if (!$promotion) {
        echo json_encode(['error' => 'not_found']);
        exit;
    }

    $services = fetchPromotionServicesWithOptions($conn, $promotionId);
    foreach ($services as &$service) {
        if (isset($service['options'])) {
            $service['options'] = array_values(array_filter(
                $service['options'],
                static fn(array $opt): bool => !empty($opt['included'])
            ));
        }
    }
    unset($service);

    echo json_encode([
        'promotion' => [
            'id' => $promotionId,
            'name' => $promotion['pm_name'] ?? 'N/A',
        ],
        'services' => array_values(array_filter($services, static fn(array $svc): bool => !empty($svc['options']))),
    ]);
    exit;
}

if ($action === 'promotion_usage') {
    header('Content-Type: application/json; charset=utf-8');

    $promotionId = isset($_GET['promotion_id']) ? (int)$_GET['promotion_id'] : 0;
    if ($promotionId <= 0) {
        echo json_encode(['error' => 'invalid_promotion']);
        exit;
    }

    $promoStmt = $conn->prepare('SELECT promotion_id, pm_name, pm_start_date, pm_end_date FROM promotion WHERE promotion_id = ?');
    if ($promoStmt === false) {
        echo json_encode(['error' => 'cannot_prepare']);
        exit;
    }
    $promoStmt->bind_param('i', $promotionId);
    $promoStmt->execute();
    $promoRes = $promoStmt->get_result();
    $promotion = $promoRes->fetch_assoc();
    $promoStmt->close();

    if (!$promotion) {
        echo json_encode(['error' => 'not_found']);
        exit;
    }

    $startDate = $promotion['pm_start_date'] ? substr((string)$promotion['pm_start_date'], 0, 10) : '';
    $endDate = $promotion['pm_end_date'] ? substr((string)$promotion['pm_end_date'], 0, 10) : '';

    $rangeStmt = $conn->prepare(
        "SELECT MIN(b.booking_date) AS min_date, MAX(b.booking_date) AS max_date
         FROM booking_seviceop bs
         INNER JOIN booking b ON b.booking_id = bs.booking_id
         INNER JOIN promotion_service_option pso ON pso.option_id = bs.option_id
         WHERE pso.promotion_id = ? AND bs.discount_booking > 0"
    );
    if ($rangeStmt) {
        $rangeStmt->bind_param('i', $promotionId);
        $rangeStmt->execute();
        $rangeRes = $rangeStmt->get_result();
        $rangeRow = $rangeRes->fetch_assoc();
        $rangeStmt->close();
        if ($startDate === '' && !empty($rangeRow['min_date'])) {
            $startDate = $rangeRow['min_date'];
        }
        if ($endDate === '' && !empty($rangeRow['max_date'])) {
            $endDate = $rangeRow['max_date'];
        }
    }

    if ($startDate === '' && $endDate !== '') {
        $startDate = $endDate;
    } elseif ($endDate === '' && $startDate !== '') {
        $endDate = $startDate;
    }

    if ($startDate === '' && $endDate === '') {
        $startDate = $endDate = date('Y-m-d');
    }

    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    $usageSql = "
        SELECT b.booking_date,
               COUNT(DISTINCT b.booking_id)           AS booking_count,
               COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum
        FROM booking_seviceop bs
        INNER JOIN booking b ON b.booking_id = bs.booking_id
        INNER JOIN promotion_service_option pso ON pso.option_id = bs.option_id
        WHERE pso.promotion_id = ?
          AND bs.discount_booking > 0
          AND b.booking_date BETWEEN ? AND ?
        GROUP BY b.booking_date
        ORDER BY b.booking_date
    ";

    $usageStmt = $conn->prepare($usageSql);
    if ($usageStmt === false) {
        echo json_encode(['error' => 'cannot_prepare']);
        exit;
    }
    $usageStmt->bind_param('iss', $promotionId, $startDate, $endDate);
    $usageStmt->execute();
    $usageRes = $usageStmt->get_result();
    $usageMap = [];
    $totalUsage = 0;
    $totalDiscount = 0.0;
    while ($row = $usageRes->fetch_assoc()) {
        $dateKey = (string)$row['booking_date'];
        $usageMap[$dateKey] = [
            'count' => (int)$row['booking_count'],
            'discount' => (float)$row['discount_sum'],
        ];
        $totalUsage += (int)$row['booking_count'];
        $totalDiscount += (float)$row['discount_sum'];
    }
    $usageStmt->close();

    $labels = [];
    $counts = [];
    $discounts = [];
    $period = new DatePeriod(new DateTimeImmutable($startDate), new DateInterval('P1D'), (new DateTimeImmutable($endDate))->modify('+1 day'));
    foreach ($period as $day) {
        $key = $day->format('Y-m-d');
        $labels[] = $key;
        $counts[] = $usageMap[$key]['count'] ?? 0;
        $discounts[] = round($usageMap[$key]['discount'] ?? 0, 2);
    }

    echo json_encode([
        'promotion' => [
            'id' => (int)$promotion['promotion_id'],
            'name' => $promotion['pm_name'] ?? 'N/A',
            'start' => $startDate,
            'end' => $endDate,
        ],
        'chart' => [
            'labels' => $labels,
            'usage' => $counts,
            'discount' => $discounts,
        ],
        'summary' => [
            'total_usage' => $totalUsage,
            'total_discount' => round($totalDiscount, 2),
        ],
    ]);
    exit;
}
// ---------------------------------------------------------------
// TABLE view preparation
// ---------------------------------------------------------------
$search = trim($_GET['q'] ?? '');
$promoStatus = $_GET['promo_status'] ?? 'all'; // all|running|upcoming|ended
$periodType = $_GET['period_type'] ?? 'all';
$periodDate = $_GET['period_date'] ?? '';
$periodMonth = $_GET['period_month'] ?? '';
$periodYear = $_GET['period_year'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$dir = strtoupper($_GET['dir'] ?? 'ASC');
$dir = $dir === 'DESC' ? 'DESC' : 'ASC';

$promoWhere = ['1=1'];
$promoTypes = '';
$promoParams = [];
if ($search !== '') {
    if (ctype_digit($search)) {
        $promoWhere[] = '(p.promotion_id = ? OR p.pm_name LIKE ?)';
        $promoTypes .= 'is';
        $promoParams[] = (int)$search;
        $promoParams[] = "%{$search}%";
    } else {
        $promoWhere[] = 'p.pm_name LIKE ?';
        $promoTypes .= 's';
        $promoParams[] = "%{$search}%";
    }
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
$promoWhereSql = implode(' AND ', $promoWhere);

$usageWhere = ['bs.discount_booking > 0'];
$usageTypes = '';
$usageParams = [];
$usageWhere[] = '(p2.pm_start_date IS NULL OR CONCAT(b.booking_date, " ", b.time_start) >= p2.pm_start_date)';
$usageWhere[] = '(p2.pm_end_date   IS NULL OR CONCAT(b.booking_date, " ", b.time_start) <= p2.pm_end_date)';

$rangeStartDateTime = null;
$rangeEndDateTime = null;
$periodType = in_array($periodType, ['all', 'date', 'month', 'year'], true) ? $periodType : 'all';

if ($periodType === 'date') {
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $periodDate);
    if ($dt) {
        $rangeStartDateTime = $dt->format('Y-m-d 00:00:00');
        $rangeEndDateTime = $dt->format('Y-m-d 23:59:59');
        $usageWhere[] = 'b.booking_date = ?';
        $usageTypes .= 's';
        $usageParams[] = $dt->format('Y-m-d');
    } else {
        $periodType = 'all';
    }
} elseif ($periodType === 'month') {
    $dt = DateTimeImmutable::createFromFormat('!Y-m', $periodMonth);
    if ($dt) {
        $rangeStartDateTime = $dt->format('Y-m-01 00:00:00');
        $rangeEndDateTime = $dt->modify('last day of this month')->format('Y-m-d 23:59:59');
        $usageWhere[] = 'YEAR(b.booking_date) = ?';
        $usageTypes .= 'i';
        $usageParams[] = (int)$dt->format('Y');
        $usageWhere[] = 'MONTH(b.booking_date) = ?';
        $usageTypes .= 'i';
        $usageParams[] = (int)$dt->format('n');
    } else {
        $periodType = 'all';
    }
} elseif ($periodType === 'year') {
    $dt = DateTimeImmutable::createFromFormat('!Y', $periodYear);
    if ($dt) {
        $rangeStartDateTime = $dt->format('Y-01-01 00:00:00');
        $rangeEndDateTime = $dt->format('Y-12-31 23:59:59');
        $usageWhere[] = 'YEAR(b.booking_date) = ?';
        $usageTypes .= 'i';
        $usageParams[] = (int)$dt->format('Y');
    } else {
        $periodType = 'all';
    }
}

if ($rangeStartDateTime !== null && $rangeEndDateTime !== null) {
    $promoWhere[] = '((p.pm_start_date IS NULL OR p.pm_start_date <= ?) AND (p.pm_end_date IS NULL OR p.pm_end_date >= ?))';
    $promoTypes .= 'ss';
    $promoParams[] = $rangeEndDateTime;
    $promoParams[] = $rangeStartDateTime;
}

$usageWhereSql = implode(' AND ', $usageWhere);

$promotionColumns = getPromotionColumns($conn);
$listSelectFields = [
    'p.promotion_id',
    'p.pm_name',
    'p.pm_start_date',
    'p.pm_end_date',
];

if (in_array('pm_created_at', $promotionColumns, true)) {
    $listSelectFields[] = 'p.pm_created_at';
} else {
    $listSelectFields[] = 'NULL AS pm_created_at';
}

if (in_array('description', $promotionColumns, true)) {
    $listSelectFields[] = 'p.description';
} else {
    $listSelectFields[] = "'' AS description";
}

if (in_array('percent', $promotionColumns, true)) {
    $listSelectFields[] = 'p.percent';
} else {
    $listSelectFields[] = 'NULL AS percent';
}

if (in_array('discount', $promotionColumns, true)) {
    $listSelectFields[] = 'p.discount';
} else {
    $listSelectFields[] = 'NULL AS discount';
}

$listSelectFields = array_merge($listSelectFields, [
    'COALESCE(svc.service_count, 0) AS service_count',
    'COALESCE(usage_stat.booking_count, 0) AS booking_count',
    'COALESCE(usage_stat.discount_sum, 0) AS discount_sum',
]);

$listSelect = implode(",\n        ", $listSelectFields);

$sortMap = [
    'name' => 'p.pm_name ' . $dir,
    'popularity' => 'COALESCE(usage_stat.booking_count,0) ' . $dir,
];
$orderBy = $sortMap[$sort] ?? $sortMap['name'];

// Count total rows for pagination
$countSql = "SELECT COUNT(*) AS c FROM promotion p WHERE {$promoWhereSql}";
$countStmt = $conn->prepare($countSql);
if (!empty($promoParams)) {
    $countStmt->bind_param($promoTypes, ...$promoParams);
}
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();

$perPage = 20;
$pages = max(1, (int)ceil($totalRows / $perPage));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $perPage;

$usageSubquery = "
    SELECT pso.promotion_id,
           COUNT(DISTINCT b.booking_id)           AS booking_count,
           COALESCE(SUM(bs.discount_booking), 0)  AS discount_sum
    FROM booking_seviceop bs
    JOIN booking b ON b.booking_id = bs.booking_id
    JOIN promotion_service_option pso ON pso.option_id = bs.option_id
    JOIN promotion p2 ON p2.promotion_id = pso.promotion_id
    WHERE {$usageWhereSql}
    GROUP BY pso.promotion_id
";

$listSql = "
    SELECT
        {$listSelect}
    FROM promotion p
    LEFT JOIN (
        SELECT promotion_id, COUNT(DISTINCT service_id) AS service_count
        FROM promotion_service
        GROUP BY promotion_id
    ) svc ON svc.promotion_id = p.promotion_id
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
    'discount_sum' => 0.0,
];
foreach ($rows as $row) {
    $tableTotals['booking_count'] += (int)$row['booking_count'];
    $tableTotals['discount_sum'] += (float)$row['discount_sum'];
}

$baseQuery = $_GET;
unset($baseQuery['page'], $baseQuery['tab'], $baseQuery['action']);
$baseQuery['tab'] = 'table';
$pageUrl = function (int $target) use ($baseQuery): string {
    $query = $baseQuery;
    $query['page'] = $target;
    return '?' . http_build_query($query);
};

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
<html lang="en">
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

  <div class="pagetitle"><h1>Promotion Report</h1></div>

  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-table" type="button">Table</button></li>
  </ul>

  <div class="tab-content">
    <!-- TABLE TAB -->
    <div class="tab-pane fade show active" id="tab-table">
      <div class="card mt-3"><div class="card-body">
        <div id="table-view">
          <div class="d-flex flex-wrap tab-toolbar mb-3">
          <form class="row g-2 align-items-end flex-grow-1" method="get">
            <input type="hidden" name="tab" value="table">
            <div class="col-sm-6 col-lg-3">
              <label class="form-label small-label">Search (ID or name)</label>
              <input type="text" class="form-control" name="q" value="<?=esc($search)?>" placeholder="e.g. 123 or promotion name">
            </div>
            <div class="col-sm-4 col-lg-2">
              <label class="form-label small-label">Promotion status</label>
              <select class="form-select" name="promo_status">
                <option value="all" <?= $promoStatus==='all'?'selected':'' ?>>All</option>
                <option value="running" <?= $promoStatus==='running'?'selected':'' ?>>Running</option>
                <option value="upcoming" <?= $promoStatus==='upcoming'?'selected':'' ?>>Upcoming</option>
                <option value="ended" <?= $promoStatus==='ended'?'selected':'' ?>>Ended</option>
              </select>
            </div>
            <div class="col-sm-4 col-lg-2">
              <label class="form-label small-label">Date range</label>
              <select class="form-select" name="period_type" id="period_type">
                <option value="all" <?= $periodType==='all'?'selected':'' ?>>All time</option>
                <option value="date" <?= $periodType==='date'?'selected':'' ?>>Specific dates</option>
                <option value="month" <?= $periodType==='month'?'selected':'' ?>>Month</option>
                <option value="year" <?= $periodType==='year'?'selected':'' ?>>Year</option>
              </select>
            </div>
            <div class="col-sm-6 col-lg-3 period-input <?= $periodType==='date'?'':'d-none' ?>" data-period="date">
              <label class="form-label small-label">Choose date</label>
              <input type="date" class="form-control" name="period_date" value="<?=esc($periodDate)?>">
            </div>
            <div class="col-sm-6 col-lg-3 period-input <?= $periodType==='month'?'':'d-none' ?>" data-period="month">
              <label class="form-label small-label">Choose month</label>
              <input type="month" class="form-control" name="period_month" value="<?=esc($periodMonth)?>">
            </div>
            <div class="col-sm-4 col-lg-2 period-input <?= $periodType==='year'?'':'d-none' ?>" data-period="year">
              <label class="form-label small-label">Choose year</label>
              <input type="number" class="form-control" name="period_year" value="<?=esc($periodYear)?>" min="2000" max="2100" step="1">
            </div>
            <div class="col-sm-6 col-lg-3">
              <label class="form-label small-label">Sort by</label>
              <div class="d-flex gap-2">
                <select class="form-select" name="sort" id="sort_field">
                  <option value="name" <?= $sort==='name'?'selected':'' ?>>Name</option>
                  <option value="popularity" <?= $sort==='popularity'?'selected':'' ?>>Popularity</option>
                </select>
                <select class="form-select" name="dir" id="sort_direction">
                  <option value="ASC" <?= $dir==='ASC'?'selected':'' ?>><?= $sort==='name' ? 'A - Z' : 'Low to High' ?></option>
                  <option value="DESC" <?= $dir==='DESC'?'selected':'' ?>><?= $sort==='name' ? 'Z - A' : 'High to Low' ?></option>
                </select>
              </div>
            </div>
            <div class="col-auto">
              <button class="btn btn-primary"><i class="bi bi-search"></i> Apply filters</button>
            </div>
          </form>
          <div class="ms-auto d-flex flex-column flex-sm-row align-items-sm-center gap-2 mt-3 mt-sm-0">
            <span class="badge bg-primary">Promotions: <?=number_format($totalRows)?></span>
            <span class="badge bg-info text-dark">Usage (this page) <?=number_format($tableTotals['booking_count'])?></span>
            <span class="badge bg-danger">Discount total (this page) -<?=number_format($tableTotals['discount_sum'],2)?></span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Promotion name</th>
                <th>Status</th>
                <th>Period</th>
                <th class="text-center">Services</th>
                <th class="text-end">Usage count</th>
                <th class="text-end">Total discount</th>
              </tr>
            </thead>
            <tbody id="promotionTableBody">
              <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-center text-muted">No data found</td></tr>
              <?php else: foreach ($rows as $row):
                $status = promotionStatus($row['pm_start_date'] ?? '', $row['pm_end_date'] ?? '');
                $badgeClass = promotion_status_badge_class($status);
                $statusLabel = promotionStatusLabel($status);
                $serviceCount = (int)$row['service_count'];
                $bookingCount = (int)$row['booking_count'];
              ?>
              <tr>
                <td><?= number_format((int)$row['promotion_id']) ?></td>
                <td>
                  <div class="fw-semibold"><?= esc($row['pm_name']) ?></div>
                  <?php if (!empty($row['description'])): ?>
                    <div class="text-muted small text-wrap" style="max-width:320px;">
                      <?= esc(mb_strimwidth($row['description'], 0, 80, '…', 'UTF-8')) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?=$badgeClass?>"><?= esc($statusLabel) ?></span></td>
                <td>
                  <div><?= esc(formatDateTimeDisplay($row['pm_start_date'] ?? '')) ?></div>
                  <div class="text-muted small">to <?= esc(formatDateTimeDisplay($row['pm_end_date'] ?? '')) ?></div>
                </td>
                <td class="text-center">
                  <?php if ($serviceCount > 0): ?>
                    <button type="button" class="btn btn-link p-0 service-detail-btn" data-promotion-id="<?=$row['promotion_id']?>" data-promotion-name="<?=esc($row['pm_name'])?>">
                      <?= number_format($serviceCount) ?>
                    </button>
                  <?php else: ?>
                    <span class="text-muted">0</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary usage-chart-btn" data-promotion-id="<?=$row['promotion_id']?>" data-promotion-name="<?=esc($row['pm_name'])?>" data-start="<?=esc($row['pm_start_date'] ?? '')?>" data-end="<?=esc($row['pm_end_date'] ?? '')?>">
                    <?= number_format($bookingCount) ?>
                  </button>
                </td>
                <td class="text-end text-danger">-<?= number_format((float)$row['discount_sum'], 2) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot>
              <tr class="table-light">
                <th colspan="5" class="text-end">Total (this page)</th>
                <th class="text-end"><?= number_format($tableTotals['booking_count']) ?></th>
                <th class="text-end text-danger">-<?= number_format($tableTotals['discount_sum'], 2) ?></th>
              </tr>
            </tfoot>
            <?php endif; ?>
          </table>
        </div>

        <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="text-muted">Page <?=number_format($page)?> / <?=number_format($pages)?></span>
          <div class="btn-group">
            <?php if ($page > 1): ?>
              <a class="btn btn-outline-secondary" href="<?=esc($pageUrl($page-1))?>"><i class="bi bi-chevron-left"></i> Previous</a>
            <?php else: ?>
              <span class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-left"></i> Previous</span>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
              <a class="btn btn-outline-primary" href="<?=esc($pageUrl($page+1))?>">Next <i class="bi bi-chevron-right"></i></a>
            <?php else: ?>
              <span class="btn btn-outline-primary disabled">Next <i class="bi bi-chevron-right"></i></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        </div> <!-- /#table-view -->

        <div id="usage-view" class="d-none">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
              <h5 class="mb-1" id="usageTitle">-</h5>
              <div class="text-muted small" id="usageRange"></div>
            </div>
            <button type="button" class="btn btn-outline-secondary" id="usageBack"><i class="bi bi-arrow-left"></i> Back to table</button>
          </div>
          <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge bg-primary">Total usage <span id="usageTotal">0</span></span>
            <span class="badge bg-danger">Total discount <span id="usageDiscount">0.00</span></span>
          </div>
          <div class="ratio ratio-16x9">
            <canvas id="usageLineChart"></canvas>
          </div>
        </div>

      </div></div>
    </div>

  </div>

</main>

<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="serviceModalLabel">Service details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="serviceModalContent" class="vstack gap-3">
          <div class="text-center text-muted">Loading...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const periodSelect=document.getElementById('period_type');
const periodInputs=document.querySelectorAll('.period-input');
function updatePeriodInputs(){
  const value=periodSelect?.value||'all';
  periodInputs.forEach(el=>{
    const shouldShow=el.dataset.period===value;
    el.classList.toggle('d-none',!shouldShow);
  });
}
periodSelect?.addEventListener('change',updatePeriodInputs);
updatePeriodInputs();

const sortField=document.getElementById('sort_field');
const sortDirection=document.getElementById('sort_direction');
function updateSortDirectionLabels(){
  if(!sortDirection) return;
  const asc=sortDirection.querySelector('option[value="ASC"]');
  const desc=sortDirection.querySelector('option[value="DESC"]');
  if(!asc||!desc) return;
  if((sortField?.value||'name')==='name'){
    asc.textContent='A - Z';
    desc.textContent='Z - A';
  }else{
    asc.textContent='Low to High';
    desc.textContent='High to Low';
  }
}
sortField?.addEventListener('change',updateSortDirectionLabels);
updateSortDirectionLabels();

const tableView=document.getElementById('table-view');
const usageView=document.getElementById('usage-view');
const usageBack=document.getElementById('usageBack');
const usageTitle=document.getElementById('usageTitle');
const usageRange=document.getElementById('usageRange');
const usageTotal=document.getElementById('usageTotal');
const usageDiscount=document.getElementById('usageDiscount');
const usageCanvas=document.getElementById('usageLineChart');
let usageChart;

function showTableView(){
  usageView?.classList.add('d-none');
  tableView?.classList.remove('d-none');
  if(usageChart){
    usageChart.destroy();
    usageChart=null;
  }
}

usageBack?.addEventListener('click',()=>{
  showTableView();
});

const serviceModalElement=document.getElementById('serviceModal');
const serviceModal=serviceModalElement?new bootstrap.Modal(serviceModalElement):null;
const serviceModalLabel=document.getElementById('serviceModalLabel');
const serviceModalContent=document.getElementById('serviceModalContent');

const nf0=v=>Number(v||0).toLocaleString();
const nf2=v=>Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
function formatDisplayDate(str){
  if(!str) return '-';
  const match=/^(\d{4})-(\d{2})-(\d{2})/.exec(str);
  if(!match) return str;
  const year=parseInt(match[1],10);
  const month=parseInt(match[2],10);
  const day=parseInt(match[3],10);
  if(Number.isNaN(year)||Number.isNaN(month)||Number.isNaN(day)) return str;
  return new Date(year,month-1,day).toLocaleDateString(undefined,{year:'numeric',month:'short',day:'2-digit'});
}

const tableBody=document.getElementById('promotionTableBody');
tableBody?.addEventListener('click',async event=>{
  const serviceBtn=event.target.closest('.service-detail-btn');
  if(serviceBtn){
    if(!serviceModal||!serviceModalContent) return;
    const promotionId=serviceBtn.dataset.promotionId||'';
    serviceModalLabel.textContent=`Service details - ${serviceBtn.dataset.promotionName||''}`;
    serviceModalContent.innerHTML='<div class="text-center text-muted py-3">Loading...</div>';
    serviceModal.show();
    try{
      const url=new URL(location.href);
      url.search='';
      url.searchParams.set('action','promotion_services');
      url.searchParams.set('promotion_id',promotionId);
      const res=await fetch(url.toString(),{cache:'no-store'});
      if(!res.ok){
        throw new Error('network_error');
      }
      const data=await res.json();
      const services=Array.isArray(data.services)?data.services:[];
      if(services.length===0){
        serviceModalContent.innerHTML='<div class="text-center text-muted py-3">No services are linked to this promotion.</div>';
        return;
      }
      serviceModalContent.innerHTML='';
      services.forEach(service=>{
        const wrapper=document.createElement('div');
        wrapper.className='border rounded p-3';
        const title=document.createElement('h6');
        title.className='mb-2';
        title.textContent=service.service_name||'Unnamed service';
        wrapper.appendChild(title);
        const stack=document.createElement('div');
        stack.className='vstack gap-2';
        (service.options||[]).forEach(option=>{
          const item=document.createElement('div');
          item.className='border rounded p-2';
          const duration=option.duration?`${option.duration} minutes`:'Service option';
          const basePrice=nf2(option.price);
          const finalPrice=nf2(option.final_price||option.price);
          const percent=Number(option.discount_percent||0);
          const amount=Number(option.discount_amount||0);
          const discountParts=[];
          if(percent>0){discountParts.push(`${percent}%`);}
          if(amount>0){discountParts.push(`฿${nf2(amount)}`);}
          const discountText=discountParts.length>0?discountParts.join(' / '):'No discount';
          item.innerHTML=
            `<div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
               <div>
                 <div class="fw-semibold">${duration}</div>
                 <div class="text-muted small">Base price ฿${basePrice}</div>
               </div>
               <div class="text-end">
                 <div class="text-danger fw-semibold">-${discountText}</div>
                 <div class="text-success small">New price ฿${finalPrice}</div>
               </div>
             </div>`;
          stack.appendChild(item);
        });
        if(!stack.childElementCount){
          const empty=document.createElement('div');
          empty.className='text-muted small';
          empty.textContent='No service options are linked to this promotion.';
          stack.appendChild(empty);
        }
        wrapper.appendChild(stack);
        serviceModalContent.appendChild(wrapper);
      });
    }catch(err){
      console.error(err);
      serviceModalContent.innerHTML='<div class="text-center text-danger py-3">Unable to load service data.</div>';
    }
    return;
  }

  const usageBtn=event.target.closest('.usage-chart-btn');
  if(usageBtn){
    event.preventDefault();
    usageTitle.textContent=`Promotion usage: ${usageBtn.dataset.promotionName||''}`;
    usageRange.textContent='Loading data...';
    usageTotal.textContent='0';
    usageDiscount.textContent='0.00';
    usageView?.classList.remove('d-none');
    tableView?.classList.add('d-none');
    if(usageChart){
      usageChart.destroy();
      usageChart=null;
    }
    try{
      const url=new URL(location.href);
      url.search='';
      url.searchParams.set('action','promotion_usage');
      url.searchParams.set('promotion_id',usageBtn.dataset.promotionId||'');
      const res=await fetch(url.toString(),{cache:'no-store'});
      if(!res.ok){
        throw new Error('network_error');
      }
      const data=await res.json();
      const labels=Array.isArray(data.chart?.labels)?data.chart.labels:[];
      const usageData=Array.isArray(data.chart?.usage)?data.chart.usage:[];
      usageRange.textContent=`Range ${formatDisplayDate(data.promotion?.start)} - ${formatDisplayDate(data.promotion?.end)}`;
      usageTotal.textContent=nf0(data.summary?.total_usage||0);
      usageDiscount.textContent=`-${nf2(data.summary?.total_discount||0)}`;
      if(usageCanvas){
        const ctx=usageCanvas.getContext('2d');
        usageChart=new Chart(ctx,{
          type:'line',
          data:{
            labels,
            datasets:[{
              label:'Usage count',
              data:usageData,
              borderColor:'#0d6efd',
              backgroundColor:'rgba(13,110,253,0.15)',
              tension:0.3,
              fill:true,
              pointRadius:3,
            }]
          },
          options:{
            responsive:true,
            maintainAspectRatio:false,
            scales:{
              x:{title:{display:true,text:'Timeline'}},
              y:{beginAtZero:true,title:{display:true,text:'Usage count'}}
            },
            plugins:{
              legend:{display:true},
              tooltip:{mode:'index',intersect:false}
            }
          }
        });
      }
    }catch(err){
      console.error(err);
      usageRange.textContent='Unable to load the chart.';
    }
  }
});
</script>
</body>
</html>
