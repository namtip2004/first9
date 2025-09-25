<?php
session_start();
require_once("connect_db.php");
require_once __DIR__ . '/../booking_status.php';

$serviceSql    = "SELECT * FROM service";
$serviceResult = mysqli_query($conn, $serviceSql);

if (!$serviceResult) {
    die("Query Error: " . mysqli_error($conn));
}

$staffMembers     = [];
$staffQuery       = "SELECT * FROM staff WHERE st_level != 'admin' ORDER BY staff_name";
$staffQueryResult = mysqli_query($conn, $staffQuery);

if ($staffQueryResult) {
    while ($row = mysqli_fetch_assoc($staffQueryResult)) {
        $staffMembers[] = $row;
    }
}

$staffSchedulesMap = [];

if (!empty($staffMembers)) {
    $staffIds = array_map('intval', array_column($staffMembers, 'staff_id'));
    $staffIds = array_filter($staffIds, static function ($id) {
        return $id > 0;
    });

    if (!empty($staffIds)) {
        $scheduleSql = "
            SELECT
                b.booking_id,
                b.staff_id,
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
            WHERE b.staff_id IN (" . implode(',', $staffIds) . ")
            GROUP BY b.booking_id
            ORDER BY b.booking_date DESC, b.time_start DESC";

        $scheduleResult = mysqli_query($conn, $scheduleSql);
        if ($scheduleResult) {
            while ($scheduleRow = mysqli_fetch_assoc($scheduleResult)) {
                $staffId = (int) ($scheduleRow['staff_id'] ?? 0);
                if ($staffId > 0) {
                    $staffSchedulesMap[$staffId][] = $scheduleRow;
                }
            }
        }
    }
}

$staffScheduleData = [];

foreach ($staffMembers as $staffMember) {
    $staffId  = (int) $staffMember['staff_id'];
    $schedules = $staffSchedulesMap[$staffId] ?? [];
    $bookings  = [];
    $events    = [];

    foreach ($schedules as $schedule) {
        if (empty($schedule['booking_id']) || empty($schedule['booking_date']) || empty($schedule['time_start']) || empty($schedule['time_end'])) {
            continue;
        }

        $statusCode  = booking_status_code($schedule['status'] ?? null);
        $statusLabel = booking_status_label($statusCode);
        $statusBadge = booking_status_badge_class($statusCode);
        $timeRange   = trim(($schedule['time_start'] ?? '') . ' - ' . ($schedule['time_end'] ?? ''));
        $priceNumber = number_format((float) ($schedule['final_price'] ?? 0), 2);

        $bookings[] = [
            'booking_id'    => (int) $schedule['booking_id'],
            'date'          => $schedule['booking_date'] ?? '',
            'time'          => $timeRange,
            'customer'      => $schedule['customer_name'] ?? 'Unknown Customer',
            'services'      => $schedule['services'] ?? 'No Services',
            'price'         => $priceNumber,
            'price_display' => '€' . $priceNumber,
            'status_label'  => $statusLabel,
            'status_badge'  => $statusBadge,
        ];

        $events[] = [
            'id'            => (int) $schedule['booking_id'],
            'title'         => $schedule['customer_name'] ?? 'Unknown Customer',
            'start'         => ($schedule['booking_date'] ?? '') . 'T' . ($schedule['time_start'] ?? ''),
            'end'           => ($schedule['booking_date'] ?? '') . 'T' . ($schedule['time_end'] ?? ''),
            'extendedProps' => [
                'bookingId' => (int) $schedule['booking_id'],
                'date'      => $schedule['booking_date'] ?? '',
                'time'      => $timeRange,
                'customer'  => $schedule['customer_name'] ?? 'Unknown Customer',
                'services'  => $schedule['services'] ?? 'No Services',
                'price'     => '€' . $priceNumber,
                'status'    => $statusLabel,
            ],
        ];
    }

    $staffScheduleData[$staffId] = [
        'name'     => $staffMember['staff_name'] ?? '',
        'bookings' => $bookings,
        'events'   => $events,
    ];
}

$staffScheduleJson = json_encode($staffScheduleData, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Service Table</h1>
      <nav>
        <ol class="breadcrumb">
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title">couse</h5> -->

              <!-- Table with stripped rows -->
              <div class="text-end mb-2">
                <a href="form_service.php" class="btn btn-success">+ add service</a>
              </div>

              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>NO.</th>
                    <th>service Name</th>
                    <th>description</th>
                    <th>status</th>
                    <th>Detail</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $i = 1;
                  while ($row = mysqli_fetch_assoc($serviceResult)) { ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['service_name']) ?></td>
                      <td><?= htmlspecialchars($row['description']) ?></td>
                      <td><?= number_format($row['is_active']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="service_detail.php?id=<?= $row['service_id'] ?>">Detail</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="service_update_form.php?id=<?= $row['service_id'] ?>">Edit</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" href="service_delete.php?id=<?= $row['service_id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this service\'s data?');">Delete</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

              <!-- End Table with stripped rows -->

            </div>
          </div>

          <div class="card mt-4">
            <div class="card-body">
              <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div>
                  <h5 class="card-title mb-1">Staff Schedules</h5>
                  <p class="text-muted mb-0">Select a staff member to review their upcoming bookings.</p>
                </div>
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                  <label for="service-staff-select" class="form-label mb-0">Staff</label>
                  <select id="service-staff-select" class="form-select" <?= empty($staffMembers) ? 'disabled' : ''; ?>>
                    <?php foreach ($staffMembers as $staff): ?>
                      <option value="<?= (int) $staff['staff_id']; ?>"><?= htmlspecialchars($staff['staff_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <?php if (empty($staffMembers)): ?>
                <div class="alert alert-warning mb-0" role="alert">
                  No staff members available to display schedules.
                </div>
              <?php else: ?>
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-2">
                  <h6 class="mb-0" id="service-schedule-subtitle"></h6>
                  <div class="btn-group" id="service-schedule-toggle" role="group" aria-label="Schedule view toggle">
                    <button type="button" class="btn btn-primary" id="service-btn-table">Table View</button>
                    <button type="button" class="btn btn-outline-primary" id="service-btn-calendar">Calendar View</button>
                  </div>
                </div>

                <div id="service-schedule-detail" class="border rounded p-3 mb-3 d-none">
                  <h6 class="mb-2">Booking Details</h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <p class="mb-1"><strong>Booking ID:</strong> <span id="service-detail-booking">-</span></p>
                      <p class="mb-1"><strong>Date:</strong> <span id="service-detail-date">-</span></p>
                      <p class="mb-1"><strong>Time:</strong> <span id="service-detail-time">-</span></p>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-1"><strong>Customer:</strong> <span id="service-detail-customer">-</span></p>
                      <p class="mb-1"><strong>Services:</strong> <span id="service-detail-services">-</span></p>
                      <p class="mb-1"><strong>Price:</strong> <span id="service-detail-price">-</span></p>
                      <p class="mb-1"><strong>Status:</strong> <span id="service-detail-status">-</span></p>
                    </div>
                  </div>
                </div>

                <div id="service-table-view" class="table-responsive">
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
                    <tbody id="service-schedule-table-body"></tbody>
                  </table>
                </div>

                <div id="service-calendar-view" class="mt-4 d-none">
                  <div id="service-schedule-calendar"></div>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
  <style>
    #service-schedule-calendar {
      max-width: 100%;
      margin: 0 auto;
    }

    .fc-event {
      cursor: pointer;
    }

    #service-schedule-toggle .btn + .btn {
      margin-left: 0.5rem;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var scheduleData = <?= $staffScheduleJson ?? '{}'; ?>;
      var staffSelect = document.getElementById('service-staff-select');
      if (!staffSelect) {
        return;
      }

      var subtitle = document.getElementById('service-schedule-subtitle');
      if (!subtitle) {
        return;
      }
      var tableView = document.getElementById('service-table-view');
      var calendarView = document.getElementById('service-calendar-view');
      var tableButton = document.getElementById('service-btn-table');
      var calendarButton = document.getElementById('service-btn-calendar');
      var detailBox = document.getElementById('service-schedule-detail');
      var detailFields = {
        booking: document.getElementById('service-detail-booking'),
        date: document.getElementById('service-detail-date'),
        time: document.getElementById('service-detail-time'),
        customer: document.getElementById('service-detail-customer'),
        services: document.getElementById('service-detail-services'),
        price: document.getElementById('service-detail-price'),
        status: document.getElementById('service-detail-status')
      };
      var tableBody = document.getElementById('service-schedule-table-body');
      var calendarEl = document.getElementById('service-schedule-calendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        dayMaxEventRows: 3,
        locale: 'en',
        events: []
      });
      calendar.render();

      function setActiveButton(active, inactive) {
        active.classList.add('btn-primary');
        active.classList.remove('btn-outline-primary');
        inactive.classList.add('btn-outline-primary');
        inactive.classList.remove('btn-primary');
      }

      function showTableView() {
        if (!tableView || !calendarView) {
          return;
        }
        tableView.classList.remove('d-none');
        calendarView.classList.add('d-none');
        setActiveButton(tableButton, calendarButton);
      }

      function showCalendarView() {
        if (!tableView || !calendarView) {
          return;
        }
        tableView.classList.add('d-none');
        calendarView.classList.remove('d-none');
        setActiveButton(calendarButton, tableButton);
      }

      function updateDetail(detail) {
        if (!detail || (!detail.booking_id && !detail.customer && !detail.services)) {
          detailBox.classList.add('d-none');
          return;
        }

        detailFields.booking.textContent = detail.booking_id || '-';
        detailFields.date.textContent = detail.date || '-';
        detailFields.time.textContent = detail.time || '-';
        detailFields.customer.textContent = detail.customer || '-';
        detailFields.services.textContent = detail.services || '-';
        detailFields.price.textContent = detail.price || '-';
        detailFields.status.textContent = detail.status || '-';
        detailBox.classList.remove('d-none');
      }

      function renderSchedule(staffId) {
        var data = scheduleData[staffId] || { bookings: [], events: [], name: '' };
        subtitle.textContent = data.name ? 'Schedule for ' + data.name : '';

        while (tableBody.firstChild) {
          tableBody.removeChild(tableBody.firstChild);
        }

        if (data.bookings && data.bookings.length) {
          data.bookings.forEach(function (booking) {
            var row = document.createElement('tr');

            var dateCell = document.createElement('td');
            dateCell.textContent = booking.date || '-';
            row.appendChild(dateCell);

            var timeCell = document.createElement('td');
            timeCell.textContent = booking.time || '-';
            row.appendChild(timeCell);

            var customerCell = document.createElement('td');
            customerCell.textContent = booking.customer || '-';
            row.appendChild(customerCell);

            var servicesCell = document.createElement('td');
            servicesCell.textContent = booking.services || '-';
            row.appendChild(servicesCell);

            var priceCell = document.createElement('td');
            priceCell.textContent = booking.price_display || ('€' + (booking.price || '0.00'));
            row.appendChild(priceCell);

            var statusCell = document.createElement('td');
            var statusBadge = document.createElement('span');
            statusBadge.className = 'badge ' + (booking.status_badge || 'bg-secondary');
            statusBadge.textContent = booking.status_label || '-';
            statusCell.appendChild(statusBadge);
            row.appendChild(statusCell);

            var actionCell = document.createElement('td');
            var actionButton = document.createElement('button');
            actionButton.type = 'button';
            actionButton.className = 'btn btn-info btn-sm service-btn-view-booking';
            actionButton.textContent = 'View';
            actionButton.dataset.staffId = staffId;
            actionButton.dataset.bookingId = booking.booking_id;
            actionCell.appendChild(actionButton);
            row.appendChild(actionCell);

            tableBody.appendChild(row);
          });

          updateDetail({
            booking_id: data.bookings[0].booking_id || '-',
            date: data.bookings[0].date || '-',
            time: data.bookings[0].time || '-',
            customer: data.bookings[0].customer || '-',
            services: data.bookings[0].services || '-',
            price: data.bookings[0].price_display || ('€' + (data.bookings[0].price || '0.00')),
            status: data.bookings[0].status_label || '-'
          });
        } else {
          var emptyRow = document.createElement('tr');
          var emptyCell = document.createElement('td');
          emptyCell.colSpan = 7;
          emptyCell.className = 'text-center text-muted';
          emptyCell.textContent = 'No schedules found for this staff member.';
          emptyRow.appendChild(emptyCell);
          tableBody.appendChild(emptyRow);
          detailBox.classList.add('d-none');
        }

        calendar.removeAllEvents();
        if (data.events && data.events.length) {
          data.events.forEach(function (event) {
            calendar.addEvent(event);
          });
          calendar.gotoDate(data.events[0].start);
        } else {
          calendar.gotoDate(new Date());
        }

        showTableView();
      }

      tableButton.addEventListener('click', showTableView);
      calendarButton.addEventListener('click', showCalendarView);

      staffSelect.addEventListener('change', function () {
        renderSchedule(this.value);
      });

      document.getElementById('service-table-view').addEventListener('click', function (event) {
        var target = event.target.closest('.service-btn-view-booking');
        if (!target) {
          return;
        }

        var staffId = target.dataset.staffId;
        var bookingId = target.dataset.bookingId;
        var data = scheduleData[staffId];
        if (!data || !data.bookings) {
          return;
        }

        var booking = data.bookings.find(function (item) {
          return String(item.booking_id) === String(bookingId);
        });

        if (!booking) {
          return;
        }

        updateDetail({
          booking_id: booking.booking_id || '-',
          date: booking.date || '-',
          time: booking.time || '-',
          customer: booking.customer || '-',
          services: booking.services || '-',
          price: booking.price_display || ('€' + (booking.price || '0.00')),
          status: booking.status_label || '-'
        });
      });

      calendar.on('eventClick', function (info) {
        var props = info.event.extendedProps || {};
        updateDetail({
          booking_id: props.bookingId || info.event.id || '-',
          date: props.date || (info.event.startStr ? info.event.startStr.split('T')[0] : ''),
          time: props.time || '',
          customer: props.customer || '',
          services: props.services || '',
          price: props.price || '',
          status: props.status || ''
        });
      });

      if (staffSelect.value) {
        renderSchedule(staffSelect.value);
      }
    });
  </script>

</body>
</html>
