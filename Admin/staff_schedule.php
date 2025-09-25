<?php
session_start();

if (!isset($_SESSION['staff_level']) || $_SESSION['staff_level'] !== 'admin') {
    header('Location: loginadmin.php');
    exit;
}

require_once 'connect_db.php';

$staffId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$staffId) {
    echo 'Invalid staff ID.';
    exit;
}

$sql = "SELECT * FROM staff WHERE staff_id = ? AND st_level != 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $staffId);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    echo 'Staff member not found.';
    exit;
}

$scheduleSql = <<<SQL
  SELECT
    b.booking_id,
    b.booking_date,
    b.time_start,
    b.time_end,
    b.final_price,
    b.status,
    c.customer_name,
    GROUP_CONCAT(sv.service_name) AS services
  FROM booking b
  LEFT JOIN customer c ON b.customer_id = c.customer_id
  LEFT JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
  LEFT JOIN service_option so ON bs.option_id = so.option_id
  LEFT JOIN service sv ON so.service_id = sv.service_id
  WHERE b.staff_id = ?
  GROUP BY b.booking_id
  ORDER BY b.b_created_at DESC
SQL;

$stmtSchedule = $conn->prepare($scheduleSql);
$stmtSchedule->bind_param('i', $staffId);
$stmtSchedule->execute();
$scheduleResult = $stmtSchedule->get_result();
$schedules = [];
while ($row = $scheduleResult->fetch_assoc()) {
    $schedules[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Staff Schedule</title>
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <style>
    #calendar { max-width: 900px; margin: 20px auto; }
    .fc-event { cursor: pointer; }
    .table-responsive { margin-top: 20px; }
    .view-toggle { margin-bottom: 20px; }
    .fc-daygrid-day { height: 120px; overflow: hidden; }
    .fc-daygrid-day-frame { height: 100%; display: flex; flex-direction: column; overflow: hidden; }
    .fc-daygrid-day-top { flex-shrink: 0; }
    .fc-daygrid-event { font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fc-daygrid-day-events { flex-grow: 1; overflow: hidden; }
  </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'slidebar.php'; ?>

<main id="main" class="main pt-5 mt-5">
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body pt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
              <div>
                <h4 class="mb-1">Schedule for <?= htmlspecialchars($staff['staff_name']); ?></h4>
                <p class="mb-0 text-muted">Staff ID: <?= htmlspecialchars($staff['staff_id']); ?></p>
              </div>
              <a href="table_staff.php" class="btn btn-secondary mt-3 mt-md-0">Back to Staff Table</a>
            </div>

            <div class="view-toggle">
              <button class="btn btn-primary me-2" onclick="showCalendar()">Calendar View</button>
              <button class="btn btn-secondary" onclick="showTable()">Table View</button>
            </div>

            <div id="calendar-view">
              <div id="calendar"></div>
            </div>

            <div id="table-view" style="display: none;">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Customer</th>
                      <th>Services</th>
                      <th>Price (€)</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($schedules)): ?>
                      <tr>
                        <td colspan="7" class="text-center">No schedules available.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($schedules as $schedule): ?>
                        <tr>
                          <td><?= htmlspecialchars($schedule['booking_date']); ?></td>
                          <td><?= htmlspecialchars($schedule['time_start']); ?> - <?= htmlspecialchars($schedule['time_end']); ?></td>
                          <td><?= htmlspecialchars($schedule['customer_name']); ?></td>
                          <td><?= htmlspecialchars($schedule['services']); ?></td>
                          <td>€<?= number_format((float)($schedule['final_price'] ?? 0), 2); ?></td>
                          <td>
                            <?php
                              $statusCode = booking_status_code($schedule['status']);
                              $badgeClass = booking_status_badge_class($statusCode);
                              $statusText = booking_status_label($statusCode);
                            ?>
                            <span class="badge <?= htmlspecialchars($badgeClass); ?>"><?= htmlspecialchars($statusText); ?></span>
                          </td>
                          <td>
                            <button
                              class="btn btn-info btn-sm"
                              data-bs-toggle="modal"
                              data-bs-target="#scheduleModal"
                              data-booking-id="<?= htmlspecialchars($schedule['booking_id']); ?>"
                              data-date="<?= htmlspecialchars($schedule['booking_date']); ?>"
                              data-time="<?= htmlspecialchars($schedule['time_start']); ?> - <?= htmlspecialchars($schedule['time_end']); ?>"
                              data-customer="<?= htmlspecialchars($schedule['customer_name']); ?>"
                              data-services="<?= htmlspecialchars($schedule['services']); ?>"
                              data-price="€<?= number_format((float)($schedule['final_price'] ?? 0), 2); ?>"
                              data-status="<?= htmlspecialchars(booking_status_label($schedule['status'])); ?>"
                            >View</button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Schedule Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Booking ID:</strong> <span id="modal-booking-id"></span></p>
                    <p><strong>Date:</strong> <span id="modal-date"></span></p>
                    <p><strong>Time:</strong> <span id="modal-time"></span></p>
                    <p><strong>Customer:</strong> <span id="modal-customer"></span></p>
                    <p><strong>Services:</strong> <span id="modal-services"></span></p>
                    <p><strong>Price:</strong> <span id="modal-price"></span></p>
                    <p><strong>Status:</strong> <span id="modal-status"></span></p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
  function showCalendar() {
    document.getElementById('calendar-view').style.display = 'block';
    document.getElementById('table-view').style.display = 'none';
  }

  function showTable() {
    document.getElementById('calendar-view').style.display = 'none';
    document.getElementById('table-view').style.display = 'block';
  }

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      dayMaxEventRows: 3,
      locale: 'en',
      events: [
        <?php
        $totalSchedules = count($schedules);
        $index = 0;
        foreach ($schedules as $schedule):
          if (!empty($schedule['booking_id']) && !empty($schedule['booking_date']) && !empty($schedule['time_start']) && !empty($schedule['time_end'])):
        ?>
          {
            id: <?= json_encode($schedule['booking_id']); ?>,
            title: <?= json_encode($schedule['customer_name'] ?? 'Unknown Customer'); ?>,
            start: <?= json_encode($schedule['booking_date'] . 'T' . $schedule['time_start']); ?>,
            end: <?= json_encode($schedule['booking_date'] . 'T' . $schedule['time_end']); ?>,
            extendedProps: {
              customer: <?= json_encode($schedule['customer_name'] ?? 'Unknown Customer'); ?>,
              services: <?= json_encode($schedule['services'] ?? 'No Services'); ?>,
              price: <?= json_encode('€' . number_format((float)($schedule['final_price'] ?? 0), 2)); ?>,
              status: <?= json_encode(booking_status_label($schedule['status']) ?? 'Unknown'); ?>,
              time: <?= json_encode(($schedule['time_start'] ?? '') . ' - ' . ($schedule['time_end'] ?? '')); ?>
            }
          }<?= $index < $totalSchedules - 1 ? ',' : ''; ?>
        <?php
            $index++;
          endif;
        endforeach;
        ?>
      ],
      eventClick: function(info) {
        document.getElementById('modal-booking-id').textContent = info.event.id;
        document.getElementById('modal-date').textContent = info.event.start.toISOString().split('T')[0];
        document.getElementById('modal-time').textContent = info.event.extendedProps.time;
        document.getElementById('modal-customer').textContent = info.event.extendedProps.customer;
        document.getElementById('modal-services').textContent = info.event.extendedProps.services;
        document.getElementById('modal-price').textContent = info.event.extendedProps.price;
        document.getElementById('modal-status').textContent = info.event.extendedProps.status;
        new bootstrap.Modal(document.getElementById('scheduleModal')).show();
      }
    });
    calendar.render();

    document.querySelectorAll('#table-view button[data-bs-toggle="modal"]').forEach(function(button) {
      button.addEventListener('click', function() {
        document.getElementById('modal-booking-id').textContent = this.dataset.bookingId;
        document.getElementById('modal-date').textContent = this.dataset.date;
        document.getElementById('modal-time').textContent = this.dataset.time;
        document.getElementById('modal-customer').textContent = this.dataset.customer;
        document.getElementById('modal-services').textContent = this.dataset.services;
        document.getElementById('modal-price').textContent = this.dataset.price;
        document.getElementById('modal-status').textContent = this.dataset.status;
      });
    });
  });
</script>
</body>
</html>
