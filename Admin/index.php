<?php
session_start();

// ถ้ายังไม่ได้ล็อกอิน
if (!isset($_SESSION['staff_id'])) {
    header("Location: loginadmin.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'first9';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

require_once __DIR__ . '/../booking_status.php';

// Get time period from URL parameter
$period = $_GET['period'] ?? 'today';

// Function to get date range based on period
function getDateRange($period) {
    switch($period) {
        case 'today':
            return ['start' => date('Y-m-d'), 'end' => date('Y-m-d')];
        case 'week':
            return ['start' => date('Y-m-d', strtotime('-7 days')), 'end' => date('Y-m-d')];
        case 'month':
            return ['start' => date('Y-m-01'), 'end' => date('Y-m-t')];
        case 'year':
            return ['start' => date('Y-01-01'), 'end' => date('Y-12-31')];
        default:
            return ['start' => date('Y-m-d'), 'end' => date('Y-m-d')];
    }
}

$dateRange = getDateRange($period);

// Get KPI data
function getKPIData($pdo, $dateRange) {
    $data = [];
    
    // Total bookings for selected period
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings, SUM(total_price) as total_revenue, 
                          SUM(total_discount) as total_discount, SUM(final_price) as net_revenue
                          FROM booking 
                          WHERE booking_date BETWEEN ? AND ?");
    $stmt->execute([$dateRange['start'], $dateRange['end']]);
    $bookingData = $stmt->fetch();
    
    $data['total_bookings'] = $bookingData['total_bookings'] ?? 0;
    $data['total_revenue'] = $bookingData['total_revenue'] ?? 0;
    $data['total_discount'] = $bookingData['total_discount'] ?? 0;
    $data['net_revenue'] = $bookingData['net_revenue'] ?? 0;
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as total_customers FROM customer WHERE account_status = 'active'");
    $data['total_customers'] = $stmt->fetch()['total_customers'];
    
    // Active services
    $stmt = $pdo->query("SELECT COUNT(*) as active_services FROM service WHERE is_active = 1");
    $data['active_services'] = $stmt->fetch()['active_services'];
    
    // Active staff
    $stmt = $pdo->query("SELECT COUNT(*) as active_staff FROM staff WHERE st_status = 'active' OR st_status = ''");
    $data['active_staff'] = $stmt->fetch()['active_staff'];
    

    
    // Cancellation rate (assuming we can identify cancelled bookings by status)
    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM booking WHERE booking_date BETWEEN ? AND ? AND status = ?) as cancelled,
        (SELECT COUNT(*) FROM booking WHERE booking_date BETWEEN ? AND ?) as total");
    $stmt->execute([
        $dateRange['start'],
        $dateRange['end'],
        BOOKING_STATUS_CANCELLED,
        $dateRange['start'],
        $dateRange['end']
    ]);
    $cancelData = $stmt->fetch();
    $data['cancellation_rate'] = $cancelData['total'] > 0 ? round(($cancelData['cancelled'] / $cancelData['total']) * 100, 1) : 0;
    
    return $data;
}

$kpiData = getKPIData($pdo, $dateRange);

// Get recent bookings
$stmt = $pdo->prepare("SELECT b.booking_id, c.customer_name, s.staff_name, b.booking_date, 
                      b.time_start, b.time_end, b.total_price, b.total_discount, b.final_price, 
                      b.status, GROUP_CONCAT(srv.service_name SEPARATOR ' + ') as services
                      FROM booking b
                      JOIN customer c ON b.customer_id = c.customer_id
                      JOIN staff s ON b.staff_id = s.staff_id
                      JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
                      JOIN service_option so ON bs.option_id = so.option_id
                      JOIN service srv ON so.service_id = srv.service_id
                      WHERE b.booking_date BETWEEN ? AND ?
                      GROUP BY b.booking_id
                      ORDER BY b.b_created_at DESC LIMIT 10");
$stmt->execute([$dateRange['start'], $dateRange['end']]);
$recentBookings = $stmt->fetchAll();

// Get service performance
$stmt = $pdo->prepare("SELECT s.service_name, COUNT(bs.booking_detail_id) as booking_count,
                      SUM(bs.net_price) as total_revenue
                      FROM service s
                      JOIN service_option so ON s.service_id = so.service_id
                      JOIN booking_seviceop bs ON so.option_id = bs.option_id
                      JOIN booking b ON bs.booking_id = b.booking_id
                      WHERE b.booking_date BETWEEN ? AND ?
                      GROUP BY s.service_id
                      ORDER BY booking_count DESC");
$stmt->execute([$dateRange['start'], $dateRange['end']]);
$servicePerformance = $stmt->fetchAll();

// Get staff performance
$stmt = $pdo->prepare("SELECT s.staff_name, COUNT(b.booking_id) as booking_count,
                      SUM(b.final_price) as total_revenue
                      FROM staff s
                      LEFT JOIN booking b ON s.staff_id = b.staff_id AND b.booking_date BETWEEN ? AND ?
                      WHERE s.st_status = 'active' OR s.st_status = ''
                      GROUP BY s.staff_id
                      ORDER BY booking_count DESC");
$stmt->execute([$dateRange['start'], $dateRange['end']]);
$staffPerformance = $stmt->fetchAll();

// Get revenue data for chart (last 7 days)
$revenueData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT SUM(total_price) as revenue, SUM(final_price) as net_revenue 
                          FROM booking WHERE booking_date = ?");
    $stmt->execute([$date]);
    $dayData = $stmt->fetch();
    $revenueData[] = [
        'date' => date('d M', strtotime($date)),
        'revenue' => $dayData['revenue'] ?? 0,
        'net_revenue' => $dayData['net_revenue'] ?? 0
    ];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Spa Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar .nav-link {
            color: #333;
        }
        .sidebar .nav-link:hover {
            color: #0d6efd;
        }
        .sidebar .nav-link.active {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, .1);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
        .kpi-card {
            transition: transform 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Navigation -->

  <?php include("header.php"); ?>

  <?php include("slidebar.php"); ?>
  <main id="main" class="main pt-5 mt-5">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->

  
            <!-- Main content -->
            <!-- <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4"> -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">แดชบอร์ด</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?period=today" class="btn btn-sm btn-outline-secondary <?= $period == 'today' ? 'active' : '' ?>">Today</a>
                            <a href="?period=week" class="btn btn-sm btn-outline-secondary <?= $period == 'week' ? 'active' : '' ?>">This week</a>
                            <a href="?period=month" class="btn btn-sm btn-outline-secondary <?= $period == 'month' ? 'active' : '' ?>">This month</a>
                            <a href="?period=year" class="btn btn-sm btn-outline-secondary <?= $period == 'year' ? 'active' : '' ?>">This year</a>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">การจอง</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kpiData['total_bookings'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">รายได้สุทธิ</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">฿<?= number_format($kpiData['net_revenue'], 2) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ลูกค้าทั้งหมด</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kpiData['total_customers'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">บริการที่เปิดใช้งาน</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kpiData['active_services'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-spa fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional KPI Row -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-secondary kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">พนักงาน Active</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kpiData['active_staff'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-badge fa-2x text-secondary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">อัตราการยกเลิก</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $kpiData['cancellation_rate'] ?>%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-x-circle fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success kpi-card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ส่วนลดรวม</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">฿<?= number_format($kpiData['total_discount'], 2) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-percent fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Revenue Chart -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-graph-up me-2"></i>แนวโน้มรายได้ (7 วันล่าสุด)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Performance Pie Chart -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-pie-chart me-2"></i>บริการยอดนิยม</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="serviceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tables Row -->
                <div class="row mb-4">
                    <!-- Recent Bookings -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-list-ul me-2"></i>การจองล่าสุด</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>รหัสจอง</th>
                                                <th>ลูกค้า</th>
                                                <th>พนักงาน</th>
                                                <th>วันที่จอง</th>
                                                <th>เวลา</th>
                                                <th>บริการ</th>
                                                <th>ราคารวม</th>
                                                <th>ส่วนลด</th>
                                                <th>สุทธิ</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td><span class="badge bg-primary">#<?= $booking['booking_id'] ?></span></td>
                                                <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($booking['staff_name']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></td>
                                                <td>
                                                    <small><?= date('H:i', strtotime($booking['time_start'])) ?> - <?= date('H:i', strtotime($booking['time_end'])) ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['services']) ?></small>
                                                </td>
                                                <td>฿<?= number_format($booking['total_price'], 2) ?></td>
                                                <td class="text-success">-฿<?= number_format($booking['total_discount'], 2) ?></td>
                                                <td class="fw-bold">฿<?= number_format($booking['final_price'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    $statusCode = booking_status_code($booking['status']);
                                                    $statusTexts = [
                                                        BOOKING_STATUS_CONFIRMED => 'ยืนยันแล้ว',
                                                        BOOKING_STATUS_PENDING => 'รอยืนยัน',
                                                        BOOKING_STATUS_CANCELLED => 'ยกเลิก',
                                                        BOOKING_STATUS_COMPLATE => 'เสร็จสิ้น'
                                                    ];
                                                    $statusClass = booking_status_badge_class($statusCode);
                                                    $statusText = $statusTexts[$statusCode] ?? booking_status_label($statusCode);
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Reports Row -->
                <div class="row">
                    <!-- Service Performance -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-bar-chart me-2"></i>ประสิทธิภาพบริการ</h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $totalBookings = array_sum(array_column($servicePerformance, 'booking_count'));
                                foreach ($servicePerformance as $service): 
                                    $percentage = $totalBookings > 0 ? round(($service['booking_count'] / $totalBookings) * 100, 1) : 0;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><?= htmlspecialchars($service['service_name']) ?></span>
                                        <span class="text-muted"><?= $percentage ?>% (<?= $service['booking_count'] ?> ครั้ง)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Performance -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person-check me-2"></i>ประสิทธิภาพพนักงาน</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($staffPerformance as $staff): ?>
                                <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($staff['staff_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($staff['staff_name']) ?></h6>
                                            <small class="text-muted">นักบำบัด</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary"><?= $staff['booking_count'] ?> การจอง</div>
                                        <small class="text-muted">฿<?= number_format($staff['total_revenue'], 0) ?> รายได้</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Row -->
                <div class="row">
                    <!-- Financial Summary -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-cash-stack me-2"></i>สรุปการเงิน</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">รายได้รวม</span>
                                        <span class="fw-bold">฿<?= number_format($kpiData['total_revenue'], 2) ?></span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">ส่วนลดรวม</span>
                                        <span class="text-danger">-฿<?= number_format($kpiData['total_discount'], 2) ?></span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">รายได้สุทธิ</span>
                                        <span class="fw-bold text-success fs-5">฿<?= number_format($kpiData['net_revenue'], 2) ?></span>
                                    </div>
                                </div>
                                <?php
                                // Get payment status data
                                $stmt = $pdo->prepare("SELECT
                                    SUM(CASE WHEN status = ? THEN final_price ELSE 0 END) as paid,
                                    SUM(CASE WHEN status = ? THEN final_price ELSE 0 END) as pending_payment
                                    FROM booking WHERE booking_date BETWEEN ? AND ?");
                                $stmt->execute([
                                    BOOKING_STATUS_CONFIRMED,
                                    BOOKING_STATUS_PENDING,
                                    $dateRange['start'],
                                    $dateRange['end']
                                ]);
                                $paymentData = $stmt->fetch();
                                ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">ชำระแล้ว</span>
                                        <span class="text-success">฿<?= number_format($paymentData['paid'], 2) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">รอชำระ</span>
                                        <span class="text-warning">฿<?= number_format($paymentData['pending_payment'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Customer Insights -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-person-hearts me-2"></i>ข้อมูลลูกค้า</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get customer insights
                                $stmt = $pdo->prepare("SELECT 
                                    COUNT(DISTINCT CASE WHEN c.c_created_at >= ? THEN c.customer_id END) as new_customers,
                                    COUNT(DISTINCT c.customer_id) as total_customers,
                                    AVG(b.final_price) as avg_order_value,
                                    COUNT(DISTINCT CASE WHEN customer_booking_count > 1 THEN c.customer_id END) as returning_customers
                                    FROM customer c
                                    LEFT JOIN booking b ON c.customer_id = b.customer_id AND b.booking_date BETWEEN ? AND ?
                                    LEFT JOIN (
                                        SELECT customer_id, COUNT(*) as customer_booking_count 
                                        FROM booking 
                                        GROUP BY customer_id
                                    ) bc ON c.customer_id = bc.customer_id
                                    WHERE c.account_status = 'active'");
                                $stmt->execute([date('Y-m-d', strtotime('-30 days')), $dateRange['start'], $dateRange['end']]);
                                $customerInsights = $stmt->fetch();
                                
                                $returnRate = $customerInsights['total_customers'] > 0 ? 
                                    round(($customerInsights['returning_customers'] / $customerInsights['total_customers']) * 100, 0) : 0;
                                ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">ลูกค้าใหม่ (30 วัน)</span>
                                        <span class="fw-bold text-success"><?= $customerInsights['new_customers'] ?> คน</span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">ลูกค้าทั้งหมด</span>
                                        <span class="fw-bold"><?= $customerInsights['total_customers'] ?> คน</span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">มูลค่าเฉลี่ย/คน</span>
                                        <span class="fw-bold">฿<?= number_format($customerInsights['avg_order_value'] ?? 0, 2) ?></span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">อัตราลูกค้ากลับมา</span>
                                        <span class="fw-bold text-success"><?= $returnRate ?>%</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">คะแนนเฉลี่ย</span>
                                        <span class="fw-bold text-warning">4.8/5.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="position-fixed bottom-0 end-0 p-3">
        <div class="dropdown dropup">
            <button class="btn btn-primary rounded-circle" type="button" data-bs-toggle="dropdown" 
                    style="width: 60px; height: 60px;">
                <i class="bi bi-plus-lg"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="add_booking.php">
                    <i class="bi bi-calendar-plus me-2"></i>เพิ่มการจอง
                </a></li>
                <li><a class="dropdown-item" href="add_customer.php">
                    <i class="bi bi-person-plus me-2"></i>เพิ่มลูกค้า
                </a></li>
                <li><a class="dropdown-item" href="add_service.php">
                    <i class="bi bi-spa me-2"></i>เพิ่มบริการ
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Charts -->
    <script>
        // Revenue Chart
        const revenueData = <?= json_encode($revenueData) ?>;
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.date),
                datasets: [
                    {
                        label: 'รายได้รวม (฿)',
                        data: revenueData.map(d => d.revenue),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'รายได้สุทธิ (฿)',
                        data: revenueData.map(d => d.net_revenue),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + value;
                            }
                        }
                    }
                }
            }
        });

        // Service Performance Chart
        const serviceData = <?= json_encode($servicePerformance) ?>;
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const serviceChart = new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: serviceData.map(s => s.service_name),
                datasets: [{
                    data: serviceData.map(s => s.booking_count),
                    backgroundColor: [
                        '#0d6efd',
                        '#198754', 
                        '#fd7e14',
                        '#6f42c1',
                        '#dc3545',
                        '#20c997'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>