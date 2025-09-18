<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
    echo "ไม่พบ Customer ID";
    exit;
}

$customer_id = $_GET['id'];

// ดึงข้อมูลลูกค้า
$sql_customer = "SELECT customer_name FROM customer WHERE customer_id = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param("i", $customer_id);
$stmt_customer->execute();
$customer_result = $stmt_customer->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    echo "ไม่พบข้อมูลลูกค้า";
    exit;
}

// ดึงข้อมูลการจองทั้งหมดของลูกค้าคนนี้
$sql_bookings = "SELECT 
    b.booking_id, b.booking_date, b.time_start, b.time_end, b.total_price, b.total_discount, 
    b.final_price, b.discount_detail, b.note, b.status, b.b_created_at, b.evidence,
    s.staff_name
FROM booking b
LEFT JOIN staff s ON b.staff_id = s.staff_id
WHERE b.customer_id = ?
ORDER BY b.b_created_at DESC";

$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $customer_id);
$stmt_bookings->execute();
$result_bookings = $stmt_bookings->get_result();

// ดึงข้อมูลบริการที่เกี่ยวข้องกับการจอง
$booking_services = [];
$sql_services = "SELECT 
    bs.booking_id, s.service_name, bs.price_booking, bs.discount_booking, bs.net_price
FROM booking_seviceop bs
LEFT JOIN service_option so ON bs.option_id = so.option_id
LEFT JOIN service s ON so.service_id = s.service_id
WHERE bs.booking_id IN (SELECT booking_id FROM booking WHERE customer_id = ?)";
$stmt_services = $conn->prepare($sql_services);
if (!$stmt_services) {
    die("SQL Error: " . $conn->error);
}
$stmt_services->bind_param("i", $customer_id);
$stmt_services->execute();
$result_services = $stmt_services->get_result();

while ($row = $result_services->fetch_assoc()) {
    $booking_services[$row['booking_id']][] = $row;
}

// ดึงสถิติการจอง
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    COALESCE(SUM(final_price), 0) as total_spent,
    COALESCE(AVG(final_price), 0) as avg_booking_value,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings
FROM booking 
WHERE customer_id = ?";
$stmt_stats = $conn->prepare($stats_sql);
$stmt_stats->bind_param("i", $customer_id);
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">
  <div class="pagetitle">
    <h1>Bookings for <?php echo htmlspecialchars($customer['customer_name']); ?></h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="report_customer.php">Customers Report</a></li>
        <li class="breadcrumb-item active">Customer Bookings</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <!-- Statistics Cards -->
    <div class="row">
      <div class="col-xxl-3 col-md-6">
        <div class="card info-card sales-card">
          <div class="card-body">
            <h5 class="card-title">Total Bookings</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-calendar-check"></i>
              </div>
              <div class="ps-3">
                <h6><?php echo number_format($stats['total_bookings']); ?></h6>
                <span class="text-muted small pt-2">Total bookings made</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xxl-3 col-md-6">
        <div class="card info-card revenue-card">
          <div class="card-body">
            <h5 class="card-title">Total Spent</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="ps-3">
                <h6>€<?php echo number_format($stats['total_spent'], 2); ?></h6>
                <span class="text-muted small pt-2">Total spent on bookings</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xxl-3 col-md-6">
        <div class="card info-card customers-card">
          <div class="card-body">
            <h5 class="card-title">Average Booking Value</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-wallet"></i>
              </div>
              <div class="ps-3">
                <h6>€<?php echo number_format($stats['avg_booking_value'], 2); ?></h6>
                <span class="text-muted small pt-2">Average per booking</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xxl-3 col-md-6">
        <div class="card info-card">
          <div class="card-body">
            <h5 class="card-title">Booking Status</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-bar-chart"></i>
              </div>
              <div class="ps-3">
                <div class="small">
                  <span class="text-success">Confirmed: <?php echo $stats['confirmed_bookings']; ?></span><br>
                  <span class="text-warning">Pending: <?php echo $stats['pending_bookings']; ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Booking Details</h5>

            <!-- Filter Controls -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="statusFilter" class="form-label">Filter by Status:</label>
                <select class="form-select" id="statusFilter">
                  <option value="">All Status</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
              <div class="col-md-4">
                <label for="dateFilter" class="form-label">Filter by Date:</label>
                <input type="date" class="form-control" id="dateFilter">
              </div>
              <div class="col-md-4">
                <label for="searchBooking" class="form-label">Search:</label>
                <input type="text" class="form-control" id="searchBooking" placeholder="Search by staff or note...">
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="bookingTable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Booking Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Services</th>
                    <th scope="col">Staff</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Discount</th>
                    <th scope="col">Final Price</th>
                    <th scope="col">Discount Detail</th>
                    <th scope="col">Status</th>
                    <th scope="col">Evidence</th>
                    <th scope="col">Note</th>
                    <th scope="col">Created At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result_bookings->num_rows > 0): ?>
                    <?php $counter = 1; ?>
                    <?php while ($booking = $result_bookings->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                        <td><?php echo date('H:i', strtotime($booking['time_start'])) . ' - ' . date('H:i', strtotime($booking['time_end'])); ?></td>
                        <td>
                          <?php if (isset($booking_services[$booking['booking_id']])): ?>
                            <ul class="list-unstyled mb-0">
                              <?php foreach ($booking_services[$booking['booking_id']] as $service): ?>
                                <li>
                                  <span class="tag-pill"><?php echo htmlspecialchars($service['service_name']); ?></span>
                                  (€<?php echo number_format($service['net_price'], 2); ?>)
                                </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php else: ?>
                            <span class="text-muted">No services</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($booking['staff_name'] ?? 'N/A'); ?></td>
                        <td>€<?php echo number_format($booking['total_price'], 2); ?></td>
                        <td>€<?php echo number_format($booking['total_discount'], 2); ?></td>
                        <td class="text-success fw-bold">€<?php echo number_format($booking['final_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($booking['discount_detail'] ?? 'N/A'); ?></td>
                        <td>
                          <?php if ($booking['status'] === 'confirmed'): ?>
                            <span class="badge bg-success">Confirmed</span>
                          <?php elseif ($booking['status'] === 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                          <?php else: ?>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($booking['status'] ?? 'N/A'); ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if (!empty($booking['evidence'])): ?>
                            <a href="assets/img/<?php echo htmlspecialchars($booking['evidence']); ?>" 
                               data-bs-toggle="modal" 
                               data-bs-target="#evidenceModal<?php echo $booking['booking_id']; ?>"
                               title="View Evidence">
                              <i class="bi bi-image"></i>
                            </a>
                            <!-- Modal for Evidence -->
                            <div class="modal fade" id="evidenceModal<?php echo $booking['booking_id']; ?>" tabindex="-1" aria-labelledby="evidenceModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                  <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                    <img src="assets/img/<?php echo htmlspecialchars($booking['evidence']); ?>" alt="Evidence" style="max-width: 100%; height: auto;">
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">No evidence</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($booking['note'] ?? 'N/A'); ?></td>
                        <td>
                          <small class="text-muted">
                            <?php echo date('M d, Y H:i', strtotime($booking['b_created_at'])); ?>
                          </small>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="13" class="text-center">No bookings found for this customer</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Export Options -->
            <!-- <div class="row mt-3">
              <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                  <button class="btn btn-success" onclick="exportToCSV()">
                    <i class="bi bi-file-earmark-excel"></i> Export to CSV
                  </button>
                  <button class="btn btn-primary" onclick="printReport()">
                    <i class="bi bi-printer"></i> Print Report
                  </button>
                  <a href="customer_detail.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Customer Details
                  </a>
                </div>
              </div>
            </div> -->

          </div>
        </div>
      </div>
    </div>

  </section>
</main>

<?php include("footer.php"); ?>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const searchInput = document.getElementById('searchBooking');
    const table = document.getElementById('bookingTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const statusValue = statusFilter.value.toLowerCase();
        const dateValue = dateFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.cells.length < 2) continue; // Skip empty rows

            const bookingDate = row.cells[1].textContent;
            const staff = row.cells[4].textContent.toLowerCase();
            const note = row.cells[11].textContent.toLowerCase();
            const statusBadge = row.cells[9].textContent.toLowerCase();

            let showRow = true;

            // Status filter
            if (statusValue && !statusBadge.includes(statusValue)) {
                showRow = false;
            }

            // Date filter
            if (dateValue) {
                const rowDate = new Date(row.cells[1].textContent).toISOString().split('T')[0];
                if (rowDate !== dateValue) {
                    showRow = false;
                }
            }

            // Search filter
            if (searchValue && !staff.includes(searchValue) && !note.includes(searchValue)) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        }
    }

    statusFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
});

// Export to CSV function
function exportToCSV() {
    const table = document.getElementById('bookingTable');
    let csv = [];
    
    // Get headers
    const headers = [];
    const headerCells = table.querySelectorAll('thead tr th');
    headerCells.forEach(header => {
        headers.push('"' + header.textContent.trim() + '"');
    });
    csv.push(headers.join(','));
    
    // Get data rows (only visible ones)
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const rowData = [];
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                let cellText = cell.textContent.trim().replace(/\s+/g, ' ');
                rowData.push('"' + cellText + '"');
            });
            csv.push(rowData.join(','));
        }
    });
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'customer_bookings_<?php echo $customer_id; ?>_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print report function
function printReport() {
    const printWindow = window.open('', '_blank');
    const tableContent = document.getElementById('bookingTable').outerHTML;
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Booking Report for <?php echo htmlspecialchars($customer['customer_name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .badge { padding: 2px 6px; border-radius: 3px; color: white; }
                .bg-success { background-color: #198754; }
                .bg-warning { background-color: #ffc107; }
                .bg-secondary { background-color: #6c757d; }
                .tag-pill { background-color: #e0f0ff; color: #0d6efd; padding: 0.35rem 0.75rem; border-radius: 999px; }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <h1>Booking Report for <?php echo htmlspecialchars($customer['customer_name']); ?></h1>
            <p>Generated on: ${new Date().toLocaleDateString()}</p>
            ${tableContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}
</script>

</body>
</html>