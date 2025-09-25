<?php
session_start();

require_once 'connect_db.php';
require_once __DIR__ . '/../booking_status.php';

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

$defaultStaffId = null;
if (!empty($staffMembers)) {
    $defaultStaffId = (int) $staffMembers[0]['staff_id'];
}

$requestedStaffId = filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => $defaultStaffId,
    ],
]);

if ($requestedStaffId && !isset($staffScheduleData[$requestedStaffId])) {
    $requestedStaffId = $defaultStaffId;
}
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include('header.php'); ?>
  <?php include('slidebar.php'); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Staff Schedule Overview</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="table_staff.php">Staff</a></li>
          <li class="breadcrumb-item active">Schedule</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div>
                  <h5 class="card-title mb-1">Staff Member</h5>
                  <p class="text-muted mb-0">Choose a staff member to inspect their bookings.</p>
                </div>
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                  <label for="staff-schedule-select" class="form-label mb-0">Staff</label>
                  <select id="staff-schedule-select" class="form-select" <?= empty($staffMembers) ? 'disabled' : ''; ?>>
                    <?php foreach ($staffMembers as $staff): ?>
                      <option value="<?= (int) $staff['staff_id']; ?>" <?= (int) $staff['staff_id'] === $requestedStaffId ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($staff['staff_name']); ?>
                      </option>
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
                  <h6 class="mb-0" id="schedule-subtitle"></h6>
                  <div class="btn-group" id="schedule-toggle" role="group" aria-label="Schedule view toggle">
                    <button type="button" class="btn btn-primary" id="btn-table">Table View</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-calendar">Calendar View</button>
                  </div>
                </div>

                <div id="schedule-detail" class="border rounded p-3 mb-3 d-none">
                  <h6 class="mb-2">Booking Details</h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <p class="mb-1"><strong>Booking ID:</strong> <span id="detail-booking">-</span></p>
                      <p class="mb-1"><strong>Date:</strong> <span id="detail-date">-</span></p>
                      <p class="mb-1"><strong>Time:</strong> <span id="detail-time">-</span></p>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-1"><strong>Customer:</strong> <span id="detail-customer">-</span></p>
                      <p class="mb-1"><strong>Services:</strong> <span id="detail-services">-</span></p>
                      <p class="mb-1"><strong>Price:</strong> <span id="detail-price">-</span></p>
                      <p class="mb-1"><strong>Status:</strong> <span id="detail-status">-</span></p>
                    </div>
                  </div>
                </div>

                <div id="table-view" class="table-responsive">
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
                    <tbody id="schedule-table-body"></tbody>
                  </table>
                </div>

                <div id="calendar-view" class="mt-4 d-none">
                  <div id="schedule-calendar"></div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main><!-- End #main -->

  <?php include('footer.php'); ?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
  <style>
    #schedule-calendar {
      max-width: 100%;
      margin: 0 auto;
    }

    .fc-event {
      cursor: pointer;
    }

    #schedule-toggle .btn + .btn {
      margin-left: 0.5rem;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var scheduleData = <?= $staffScheduleJson ?? '{}'; ?>;
      var initialStaffId = <?= $requestedStaffId ? (int) $requestedStaffId : 'null'; ?>;
      var staffSelect = document.getElementById('staff-schedule-select');
      var subtitle = document.getElementById('schedule-subtitle');
      var tableView = document.getElementById('table-view');
      var calendarView = document.getElementById('calendar-view');
      var tableButton = document.getElementById('btn-table');
      var calendarButton = document.getElementById('btn-calendar');
      var detailBox = document.getElementById('schedule-detail');
      var detailFields = {
        booking: document.getElementById('detail-booking'),
        date: document.getElementById('detail-date'),
        time: document.getElementById('detail-time'),
        customer: document.getElementById('detail-customer'),
        services: document.getElementById('detail-services'),
        price: document.getElementById('detail-price'),
        status: document.getElementById('detail-status')
      };
      var tableBody = document.getElementById('schedule-table-body');
      var calendarEl = document.getElementById('schedule-calendar');
      var calendar = null;
      if (calendarEl) {
        calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          dayMaxEventRows: 3,
          locale: 'en',
          events: []
        });
        calendar.render();
      }

      function setActiveButton(active, inactive) {
        if (!active || !inactive) {
          return;
        }
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

        while (tableBody && tableBody.firstChild) {
          tableBody.removeChild(tableBody.firstChild);
        }

        if (tableBody && data.bookings && data.bookings.length) {
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
            actionButton.className = 'btn btn-info btn-sm btn-view-booking';
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
          if (tableBody) {
            tableBody.appendChild(emptyRow);
          }
          detailBox.classList.add('d-none');
        }

        if (calendar) {
          calendar.removeAllEvents();
          if (data.events && data.events.length) {
            data.events.forEach(function (event) {
              calendar.addEvent(event);
            });
            calendar.gotoDate(data.events[0].start);
          } else {
            calendar.gotoDate(new Date());
          }
        }

        showTableView();
      }

      if (tableButton && calendarButton) {
        tableButton.addEventListener('click', showTableView);
        calendarButton.addEventListener('click', showCalendarView);
      }

      if (staffSelect) {
        staffSelect.addEventListener('change', function () {
          var selectedId = this.value;
          var url = new URL(window.location.href);
          url.searchParams.set('staff_id', selectedId);
          window.location.href = url.toString();
        });
      }

      if (tableView) {
        tableView.addEventListener('click', function (event) {
          var target = event.target.closest('.btn-view-booking');
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
      }

      if (calendar) {
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
      }

      if (initialStaffId) {
        renderSchedule(String(initialStaffId));
      }
    });
  </script>
</body>

</html>
