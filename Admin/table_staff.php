<?php
session_start();

// ตรวจสอบว่า staff_level มีอยู่ในเซสชันหรือไม่
if (isset($_SESSION['staff_level']) && $_SESSION['staff_level'] !== 'admin') {
    header('Location: profile.php');
    exit;
}

require_once("connect_db.php");

$staffMembers      = [];
$staffQueryError   = null;
$staffQuery        = "SELECT * FROM staff WHERE st_level != 'admin' ORDER BY staff_name";
$staffQueryResult  = mysqli_query($conn, $staffQuery);

if ($staffQueryResult) {
    while ($row = mysqli_fetch_assoc($staffQueryResult)) {
        $staffMembers[] = $row;
    }
} else {
    $staffQueryError = mysqli_error($conn);
}

$selectedStaffId = isset($_GET['staff_id']) ? (int) $_GET['staff_id'] : 0;
$selectedStaff   = null;
$staffSchedules  = [];

if ($selectedStaffId > 0) {
    $staffStmt = $conn->prepare("SELECT staff_id, staff_name FROM staff WHERE staff_id = ?");
    if ($staffStmt) {
        $staffStmt->bind_param("i", $selectedStaffId);
        $staffStmt->execute();
        $staffResult = $staffStmt->get_result();
        $selectedStaff = $staffResult->fetch_assoc();
        $staffStmt->close();
    }

    if ($selectedStaff) {
        $scheduleSql = "
            SELECT
                b.booking_id,
                b.booking_date,
                b.time_start,
                b.time_end,
                b.final_price,
                b.status,
                c.customer_name,
                GROUP_CONCAT(sv.service_name SEPARATOR ', ') AS services
            FROM booking b
            LEFT JOIN customer c ON b.customer_id = c.customer_id
            LEFT JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
            LEFT JOIN service_option so ON bs.option_id = so.option_id
            LEFT JOIN service sv ON so.service_id = sv.service_id
            WHERE b.staff_id = ?
            GROUP BY b.booking_id
            ORDER BY b.b_created_at DESC";

        $scheduleStmt = $conn->prepare($scheduleSql);
        if ($scheduleStmt) {
            $scheduleStmt->bind_param("i", $selectedStaffId);
            $scheduleStmt->execute();
            $scheduleResult = $scheduleStmt->get_result();
            while ($scheduleRow = $scheduleResult->fetch_assoc()) {
                $staffSchedules[] = $scheduleRow;
            }
            $scheduleStmt->close();
        }
    }
}

$calendarEvents = [];
foreach ($staffSchedules as $schedule) {
    if (!empty($schedule['booking_id']) && !empty($schedule['booking_date']) && !empty($schedule['time_start']) && !empty($schedule['time_end'])) {
        $calendarEvents[] = [
            'id'            => (int) $schedule['booking_id'],
            'title'         => $schedule['customer_name'] ?? 'Unknown Customer',
            'start'         => $schedule['booking_date'] . 'T' . $schedule['time_start'],
            'end'           => $schedule['booking_date'] . 'T' . $schedule['time_end'],
            'extendedProps' => [
                'customer' => $schedule['customer_name'] ?? 'Unknown Customer',
                'services' => $schedule['services'] ?? 'No Services',
                'price'    => '€' . number_format((float) ($schedule['final_price'] ?? 0), 2),
                'status'   => booking_status_label($schedule['status']),
                'time'     => ($schedule['time_start'] ?? '') . ' - ' . ($schedule['time_end'] ?? ''),
            ],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Staff Table</h1>

      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title">Staff</h5> -->
              <div class="text-end mb-2">
                <a href="form_staff.php" class="btn btn-success mb-2">+ add staff</a>
              </div>

              <?php if ($staffQueryError): ?>
                <div class="alert alert-danger" role="alert">
                  <?= htmlspecialchars($staffQueryError); ?>
                </div>
              <?php endif; ?>

              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NO.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Birthday</th>
                    <th>Gmail</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Start Job</th>
                    <th>End Job</th>
                    <th>Status</th>
                    <th>Detail</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($staffMembers)): ?>
                    <?php foreach ($staffMembers as $index => $staff): ?>
                      <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($staff['staff_name']); ?></td>
                        <td><?= htmlspecialchars($staff['st_gender']); ?></td>
                        <td><?= htmlspecialchars($staff['st_age']); ?></td>
                        <td><?= htmlspecialchars($staff['st_birthday']); ?></td>
                        <td><?= htmlspecialchars($staff['st_gmail']); ?></td>
                        <td><?= htmlspecialchars($staff['st_tel']); ?></td>
                        <td><?= htmlspecialchars($staff['st_address']); ?></td>
                        <td><?= htmlspecialchars($staff['start_job']); ?></td>
                        <td><?= htmlspecialchars($staff['end_job']); ?></td>
                        <td><?= htmlspecialchars($staff['st_status']); ?></td>
                        <td>
                          <a class="btn btn-outline-primary btn-sm" href="staff_detail.php?id=<?= $staff['staff_id']; ?>">Detail</a>
                        </td>
                        <td>
                          <a class="btn btn-outline-primary btn-sm" href="staff_update_form.php?id=<?= $staff['staff_id']; ?>">Edit</a>
                        </td>
                        <td>
                          <a class="btn btn-outline-danger btn-sm" href="staff_delete.php?id=<?= $staff['staff_id']; ?>" onclick="return confirm('Are you sure you want to permanently delete this staff\'s data?');">Delete</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="14" class="text-center text-muted">No staff members found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

    <section class="section mt-4">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Staff Schedules</h5>

              <?php if (!empty($staffMembers)) : ?>
                <form method="get" class="row g-2 align-items-end">
                  <div class="col-md-6">
                    <label for="staff_id" class="form-label">Select Staff</label>
                    <select name="staff_id" id="staff_id" class="form-select">
                      <option value="">-- Select staff --</option>
                      <?php foreach ($staffMembers as $staffMember) : ?>
                        <option value="<?= (int) $staffMember['staff_id']; ?>" <?= $selectedStaffId === (int) $staffMember['staff_id'] ? 'selected' : ''; ?>>
                          <?= htmlspecialchars($staffMember['staff_name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-auto">
                    <button type="submit" class="btn btn-primary">View Schedule</button>
                  </div>
                </form>
              <?php else : ?>
                <p class="text-muted">No staff available.</p>
              <?php endif; ?>

              <?php if ($selectedStaffId > 0) : ?>
                <?php if ($selectedStaff) : ?>
                  <hr>
                  <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-3">Schedule for <?= htmlspecialchars($selectedStaff['staff_name']); ?></h6>
                    <div class="view-toggle mb-3">
                      <button class="btn btn-primary me-2" type="button" onclick="showAdminCalendar()">Calendar View</button>
                      <button class="btn btn-secondary" type="button" onclick="showAdminTable()">Table View</button>
                    </div>
                  </div>

                  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
                  <style>
                    #admin-calendar {
                      max-width: 900px;
                      margin: 20px auto;
                    }

                    .fc-event {
                      cursor: pointer;
                    }

                    .admin-table-responsive {
                      margin-top: 20px;
                    }

                    .view-toggle {
                      margin-bottom: 0;
                    }

                    .fc-daygrid-day {
                      height: 120px;
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

                  <div id="admin-calendar-view">
                    <div id="admin-calendar"></div>
                  </div>

                  <div id="admin-table-view" style="display: none;">
                    <div class="table-responsive admin-table-responsive">
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
                          <?php if (!empty($staffSchedules)) : ?>
                            <?php foreach ($staffSchedules as $schedule) : ?>
                              <tr>
                                <td><?= htmlspecialchars($schedule['booking_date']); ?></td>
                                <td><?= htmlspecialchars($schedule['time_start']); ?> - <?= htmlspecialchars($schedule['time_end']); ?></td>
                                <td><?= htmlspecialchars($schedule['customer_name']); ?></td>
                                <td><?= htmlspecialchars($schedule['services']); ?></td>
                                <td>€<?= number_format((float) ($schedule['final_price'] ?? 0), 2); ?></td>
                                <td>
                                  <?php
                                  $statusCode = booking_status_code($schedule['status']);
                                  $badgeClass = booking_status_badge_class($statusCode);
                                  $statusText = booking_status_label($statusCode);
                                  ?>
                                  <span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars($statusText); ?></span>
                                </td>
                                <td>
                                  <button class="btn btn-info btn-sm"
                                          type="button"
                                          data-bs-toggle="modal"
                                          data-bs-target="#adminScheduleModal"
                                          data-booking-id="<?= (int) $schedule['booking_id']; ?>"
                                          data-date="<?= htmlspecialchars($schedule['booking_date']); ?>"
                                          data-time="<?= htmlspecialchars($schedule['time_start']); ?> - <?= htmlspecialchars($schedule['time_end']); ?>"
                                          data-customer="<?= htmlspecialchars($schedule['customer_name']); ?>"
                                          data-services="<?= htmlspecialchars($schedule['services']); ?>"
                                          data-price="€<?= number_format((float) ($schedule['final_price'] ?? 0), 2); ?>"
                                          data-status="<?= htmlspecialchars(booking_status_label($schedule['status'])); ?>">
                                    View
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else : ?>
                            <tr>
                              <td colspan="7" class="text-center text-muted">No schedules found for this staff member.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="modal fade" id="adminScheduleModal" tabindex="-1" aria-labelledby="adminScheduleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="adminScheduleModalLabel">Schedule Details</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p><strong>Booking ID:</strong> <span id="admin-modal-booking-id"></span></p>
                          <p><strong>Date:</strong> <span id="admin-modal-date"></span></p>
                          <p><strong>Time:</strong> <span id="admin-modal-time"></span></p>
                          <p><strong>Customer:</strong> <span id="admin-modal-customer"></span></p>
                          <p><strong>Services:</strong> <span id="admin-modal-services"></span></p>
                          <p><strong>Price:</strong> <span id="admin-modal-price"></span></p>
                          <p><strong>Status:</strong> <span id="admin-modal-status"></span></p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>

                <?php else : ?>
                  <hr>
                  <p class="text-danger">Selected staff not found.</p>
                <?php endif; ?>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>

  <?php if ($selectedStaff && $selectedStaffId > 0) : ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
      function showAdminCalendar() {
        document.getElementById('admin-calendar-view').style.display = 'block';
        document.getElementById('admin-table-view').style.display = 'none';
      }

      function showAdminTable() {
        document.getElementById('admin-calendar-view').style.display = 'none';
        document.getElementById('admin-table-view').style.display = 'block';
      }

      document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('admin-calendar');
        if (calendarEl) {
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            dayMaxEventRows: 3,
            locale: 'en',
            events: <?= json_encode($calendarEvents, JSON_UNESCAPED_UNICODE); ?>,
            eventClick: function (info) {
              document.getElementById('admin-modal-booking-id').textContent = info.event.id;
              document.getElementById('admin-modal-date').textContent = info.event.start.toISOString().split('T')[0];
              document.getElementById('admin-modal-time').textContent = info.event.extendedProps.time || '';
              document.getElementById('admin-modal-customer').textContent = info.event.extendedProps.customer || '';
              document.getElementById('admin-modal-services').textContent = info.event.extendedProps.services || '';
              document.getElementById('admin-modal-price').textContent = info.event.extendedProps.price || '';
              document.getElementById('admin-modal-status').textContent = info.event.extendedProps.status || '';
              new bootstrap.Modal(document.getElementById('adminScheduleModal')).show();
            }
          });
          calendar.render();
        }

        document.querySelectorAll('#admin-table-view button[data-bs-toggle="modal"]').forEach(function (button) {
          button.addEventListener('click', function () {
            document.getElementById('admin-modal-booking-id').textContent = this.dataset.bookingId || '';
            document.getElementById('admin-modal-date').textContent = this.dataset.date || '';
            document.getElementById('admin-modal-time').textContent = this.dataset.time || '';
            document.getElementById('admin-modal-customer').textContent = this.dataset.customer || '';
            document.getElementById('admin-modal-services').textContent = this.dataset.services || '';
            document.getElementById('admin-modal-price').textContent = this.dataset.price || '';
            document.getElementById('admin-modal-status').textContent = this.dataset.status || '';
          });
        });
      });
    </script>
  <?php endif; ?>

</body>

</html>
