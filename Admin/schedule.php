<?php


session_start(); 
// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

require_once("connect_db.php");

$staff_id  = $_SESSION['staff_id'];

// ดึงข้อมูล staff
$sql = "SELECT * FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
  echo "ไม่พบข้อมูลพนักงาน";
  exit;
}

// ดึงข้อมูลตารางงานจาก booking
$schedule_sql = "
  SELECT 
    b.booking_id,
    b.booking_date,
    b.time_start,
    b.time_end,
    b.final_price,
    b.status,
    c.customer_name,
    GROUP_CONCAT(sv.service_name) as services
  FROM booking b
  LEFT JOIN customer c ON b.customer_id = c.customer_id
  LEFT JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
  LEFT JOIN service_option so ON bs.option_id = so.option_id
  LEFT JOIN service sv ON so.service_id = sv.service_id
  WHERE b.staff_id = ?
  GROUP BY b.booking_id
  ORDER BY b.b_created_at DESC
";

$stmt_schedule = $conn->prepare($schedule_sql);
$stmt_schedule->bind_param("i", $staff_id);
$stmt_schedule->execute();
$schedule_result = $stmt_schedule->get_result();
$schedules = [];
while ($row = $schedule_result->fetch_assoc()) {
  $schedules[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Staff Detail</title>  <link href="assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <style>
    #calendar { max-width: 900px; margin: 20px auto; }
    .fc-event { cursor: pointer; }
    .table-responsive { margin-top: 20px; }
    .view-toggle { margin-bottom: 20px; }

    /* ทำให้ช่องปฏิทินเป็นสี่เหลี่ยมจัตุรัส และไม่ยืด */
.fc-daygrid-day {
  height: 120px; /* ปรับตามที่ต้องการ */
  overflow: hidden;
}

.fc-daygrid-day-frame {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.fc-daygrid-day-top {
  flex-shrink: 0;
}

/* ตัดข้อความงานเกินความกว้าง */
.fc-daygrid-event {
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.fc-daygrid-day-events {
  flex-grow: 1;
  overflow: hidden;
}
  </style>
</head>
<body>

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">
  <!-- <div class="pagetitle">
    <h1>Staff Detail</h1>
  </div> -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body pt-4">

            <!-- Toggle View -->
            <div class="view-toggle">
              <button class="btn btn-primary me-2" onclick="showCalendar()">Calendar View</button>
              <button class="btn btn-secondary" onclick="showTable()">Table View</button>
            </div>

            <!-- Calendar View -->
            <div id="calendar-view">
              <div id="calendar"></div>
            </div>

            <!-- Table View -->
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
                    <?php foreach ($schedules as $schedule): ?>
                      <tr>
                        <td><?= htmlspecialchars($schedule['booking_date']) ?></td>
                        <td><?= htmlspecialchars($schedule['time_start']) ?> - <?= htmlspecialchars($schedule['time_end']) ?></td>
                        <td><?= htmlspecialchars($schedule['customer_name']) ?></td>
                        <td><?= htmlspecialchars($schedule['services']) ?></td>
                        <td>€<?= number_format($schedule['final_price'], 2) ?></td>
                        <td>
                          <?= htmlspecialchars($schedule['status']) === 'confirmed' 
                              ? '<span class="text-success">Confirmed</span>' 
                              : '<span class="text-danger">' . htmlspecialchars($schedule['status']) . '</span>' ?>
                        </td>
                        <td>
                          <button class="btn btn-info btn-sm" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#scheduleModal"
                                  data-booking-id="<?= $schedule['booking_id'] ?>"
                                  data-date="<?= htmlspecialchars($schedule['booking_date']) ?>"
                                  data-time="<?= htmlspecialchars($schedule['time_start']) ?> - <?= htmlspecialchars($schedule['time_end']) ?>"
                                  data-customer="<?= htmlspecialchars($schedule['customer_name']) ?>"
                                  data-services="<?= htmlspecialchars($schedule['services']) ?>"
                                  data-price="€<?= number_format($schedule['final_price'], 2) ?>"
                                  data-status="<?= htmlspecialchars($schedule['status']) ?>">View</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Schedule Details Modal -->
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
                  <!-- <div class="modal-footer">
                    <a id="modal-edit-link" href="#" class="btn btn-primary">Edit</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div> -->
                </div>
              </div>
            </div>
<!-- 
            <div class="text-center mt-4">
              <a href="staff_update_form.php?id=<?= $staff['staff_id'] ?>" class="btn btn-primary mt-3">Edit</a>
              <a href="table_staff.php" class="btn btn-secondary mt-3">Back</a>
            </div> -->

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>

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
      dayMaxEventRows: 3, // จำกัดสูงสุด 3 งานต่อวัน
        locale: 'en',
      events: [
        <?php 
        $total_schedules = count($schedules);
        $index = 0;
        foreach ($schedules as $schedule): 
          // ตรวจสอบว่ามีข้อมูลครบถ้วน
          if (!empty($schedule['booking_id']) && !empty($schedule['booking_date']) && !empty($schedule['time_start']) && !empty($schedule['time_end'])): 
        ?>
          {
            id: <?= json_encode($schedule['booking_id']) ?>,
            title: <?= json_encode($schedule['customer_name'] ?? 'Unknown Customer') ?>,
            start: <?= json_encode($schedule['booking_date'] . 'T' . $schedule['time_start']) ?>,
            end: <?= json_encode($schedule['booking_date'] . 'T' . $schedule['time_end']) ?>,
            extendedProps: {
              customer: <?= json_encode($schedule['customer_name'] ?? 'Unknown Customer') ?>,
              services: <?= json_encode($schedule['services'] ?? 'No Services') ?>,
              price: <?= json_encode('€' . number_format($schedule['final_price'] ?? 0, 2)) ?>,
              status: <?= json_encode($schedule['status'] ?? 'Unknown') ?>,
              time: <?= json_encode(($schedule['time_start'] ?? '') . ' - ' . ($schedule['time_end'] ?? '')) ?>
            }
          }<?= $index < $total_schedules - 1 ? ',' : '' ?>
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
        // document.getElementById('modal-edit-link').href = 'booking_update_form.php?id=' + info.event.id;
        new bootstrap.Modal(document.getElementById('scheduleModal')).show();
      }
    });
    calendar.render();

    // Table button click handler
    document.querySelectorAll('#table-view button[data-bs-toggle="modal"]').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('modal-booking-id').textContent = this.dataset.bookingId;
        document.getElementById('modal-date').textContent = this.dataset.date;
        document.getElementById('modal-time').textContent = this.dataset.time;
        document.getElementById('modal-customer').textContent = this.dataset.customer;
        document.getElementById('modal-services').textContent = this.dataset.services;
        document.getElementById('modal-price').textContent = this.dataset.price;
        document.getElementById('modal-status').textContent = this.dataset.status;
        document.getElementById('modal-edit-link').href = 'booking_update_form.php?id=' + this.dataset.bookingId;
      });
    });
  });
</script>

</body>
</html>