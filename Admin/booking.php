<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=first9', 'root', '');
$customers = $pdo->query('SELECT customer_id, customer_name FROM customer')->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query('SELECT service_id, service_name FROM service WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Booking Page</title>

    <!-- Bootstrap 5 CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <!-- Flatpickr CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
    />

    <!-- Select2 CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
      rel="stylesheet"
    />

    <style>
      .upload-box {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        position: relative;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
      }

      .upload-box.dragover {
        background: #f8f9fa;
        border-color: #0d6efd;
      }

      .upload-box input[type='file'] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
      }

      #previewImage {
        display: none;
        max-width: 100%;
        max-height: 260px;
      }

      #uploadText {
        color: #6c757d;
      }
    </style>
  </head>

  <body>
    <?php include 'header.php'; ?>
    <?php include 'slidebar.php'; ?>

    <main id="main" class="main pt-5 mt-5">
      <div class="pagetitle">
        <h1>Booking System</h1>
        <nav>
          <ol class="breadcrumb"></ol>
        </nav>
      </div>

      <section class="section">
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <form
                  id="bookingForm"
                  action="submit_booking.php"
                  method="POST"
                  enctype="multipart/form-data"
                >
                  <!-- Customer -->
                  <div class="mb-3">
                    <label for="customer" class="form-label">Select Customer</label>
                    <select
                      class="form-select"
                      id="customer"
                      name="customer_id"
                      required
                      style="width: 100%"
                    >
                      <option value="">Select a customer</option>
                      <?php foreach ($customers as $customer): ?>
                      <option value="<?= $customer['customer_id']; ?>">
                        <?= $customer['customer_id']; ?> -
                        <?= htmlspecialchars($customer['customer_name']); ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Services -->
                  <div id="servicesContainer">
                    <div class="service-row card p-3 mb-3">
                      <div class="row">
                        <div class="col-md-6">
                          <label class="form-label">Select Service</label>
                          <select
                            class="form-select service-select"
                            name="services[]"
                            onchange="loadOptions(this)"
                          >
                            <option value="">Select a service</option>
                            <?php foreach ($services as $service): ?>
                            <option value="<?= $service['service_id']; ?>">
                              <?= htmlspecialchars($service['service_name']); ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-5">
                          <label class="form-label">Select Option</label>
                          <select
                            class="form-select option-select"
                            name="options[]"
                            onchange="onOptionChange(this)"
                          >
                            <option value="">Select an option</option>
                          </select>
                        </div>
                        <div class="col-md-1">
                          <button
                            type="button"
                            class="btn btn-danger mt-4"
                            onclick="removeService(this)"
                          >
                            X
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="btn btn-primary mb-3"
                    onclick="addService()"
                  >
                    Add Another Service
                  </button>

                  <!-- Total Duration -->
                  <div class="mb-3">
                    <label class="form-label">Total Duration</label>
                    <input
                      type="text"
                      class="form-control"
                      id="totalDuration"
                      readonly
                    />
                  </div>

                  <!-- Date & Time -->
                  <div class="mb-3">
                    <label for="bookingDate" class="form-label">Select Date</label>
                    <input
                      type="text"
                      class="form-control"
                      id="bookingDate"
                      name="booking_date"
                      required
                    />
                  </div>
                  <div class="mb-3">
                    <label for="startTime" class="form-label">Select Start Time</label>
                    <select
                      class="form-select"
                      id="startTime"
                      name="start_time"
                      onchange="loadAvailableStaff()"
                      required
                    >
                      <option value="">Select a time</option>
                    </select>
                  </div>

                  <!-- Staff -->
                  <div class="mb-3">
                    <label for="staff" class="form-label">Select Staff</label>
                    <select class="form-select" id="staff" name="staff_id" required>
                      <option value="">Select a staff member</option>
                    </select>
                  </div>

                  <!-- Price Summary -->
                  <div class="summary-card">
                    <h4>Price Summary</h4>
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Service</th>
                          <th>Option</th>
                          <th>Price (€)</th>
                        </tr>
                      </thead>
                      <tbody id="priceTable"></tbody>
                      <tfoot>
                        <tr>
                          <th colspan="2">Total</th>
                          <th id="totalPrice">€0.00</th>
                        </tr>
                        <tr>
                          <th colspan="2">Discount</th>
                          <th id="discountAmount">-€0.00</th>
                        </tr>
                        <tr>
                          <th colspan="2">Final Price</th>
                          <th id="finalPrice">€0.00</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <!-- Upload evidence -->
                  <div class="col-md-12">
                    <div class="upload-box" id="uploadBox">
                      <div class="upload-text" id="uploadText">add evidence</div>
                      <input type="file" id="imgprofile" name="imgprofile" />
                      <img id="previewImage" alt="Preview" />
                    </div>
                  </div>

                  <!-- Submit -->
                  <button type="submit" class="btn btn-primary mt-3">
                    Confirm Booking
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- JS libs: jQuery first (for Select2), then Bootstrap, Select2, Flatpickr -->
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

      <script>
        // ---------- Upload box ----------
        const uploadBox = document.getElementById('uploadBox');
        const fileInput = document.getElementById('imgprofile');
        const previewImage = document.getElementById('previewImage');
        const uploadText = document.getElementById('uploadText');

        uploadBox.addEventListener('dragover', (event) => {
          event.preventDefault();
          uploadBox.classList.add('dragover');
        });

        uploadBox.addEventListener('dragleave', () => {
          uploadBox.classList.remove('dragover');
        });

        uploadBox.addEventListener('drop', (event) => {
          event.preventDefault();
          uploadBox.classList.remove('dragover');
          if (event.dataTransfer.files.length > 0) {
            fileInput.files = event.dataTransfer.files;
            showPreview(fileInput.files[0]);
          }
        });

        fileInput.addEventListener('change', () => {
          if (fileInput.files.length > 0) {
            showPreview(fileInput.files[0]);
          }
        });

        function showPreview(file) {
          const reader = new FileReader();
          reader.onload = (event) => {
            previewImage.src = event.target.result;
            previewImage.style.display = 'block';
            uploadText.style.display = 'none';
          };
          reader.readAsDataURL(file);
        }

        // ---------- Select2 for customer ----------
        document.addEventListener('DOMContentLoaded', () => {
          const customerSelect = document.getElementById('customer');
          if (customerSelect) {
            $(customerSelect).select2({
              placeholder: 'Search or select a customer',
              allowClear: true,
            });
          }
        });

        // ---------- Flatpickr ----------
        flatpickr('#bookingDate', {
          dateFormat: 'Y-m-d',
          minDate: 'today',
          onChange(selectedDates, dateStr) {
            loadAvailableTimes(dateStr);
            calculatePrices();
          },
        });

        // ---------- Helpers ----------
        function getTotalMinutes() {
          let total = 0;
          document.querySelectorAll('.option-select').forEach((select) => {
            const duration = parseInt(
              select.options[select.selectedIndex]?.dataset.duration || 0,
              10,
            );
            if (!Number.isNaN(duration)) {
              total += duration;
            }
          });
          return total;
        }

        function refreshTotalDuration() {
          const minutes = getTotalMinutes();
          document.getElementById('totalDuration').value = `${minutes} minutes`;
          const date = document.getElementById('bookingDate').value;
          if (date) {
            loadAvailableTimes(date);
          }
        }

        function onOptionChange(optionElement) {
          refreshTotalDuration();
          calculatePrices();
        }

        // ---------- Add / Remove service rows ----------
        function addService() {
          const container = document.getElementById('servicesContainer');
          const newRow = document.createElement('div');
          newRow.className = 'service-row card p-3 mb-3';
          newRow.innerHTML = `
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">Select Service</label>
                <select class="form-select service-select" name="services[]" onchange="loadOptions(this)">
                  <option value="">Select a service</option>
                  <?php foreach ($services as $service): ?>
                  <option value="<?= $service['service_id']; ?>">
                    <?= htmlspecialchars($service['service_name']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label">Select Option</label>
                <select class="form-select option-select" name="options[]" onchange="onOptionChange(this)">
                  <option value="">Select an option</option>
                </select>
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-danger mt-4" onclick="removeService(this)">
                  X
                </button>
              </div>
            </div>`;
          container.appendChild(newRow);
        }

        function removeService(button) {
          const row = button.closest('.service-row');
          row.remove();
          refreshTotalDuration();
          calculatePrices();
        }

        // ---------- Load options for a given service (row-relative) ----------
        function loadOptions(serviceSelectElement) {
          const row = serviceSelectElement.closest('.service-row');
          const optionSelect = row.querySelector('.option-select');
          const serviceId = serviceSelectElement.value;

          optionSelect.innerHTML = '<option value="">Select an option</option>';
          if (!serviceId) {
            refreshTotalDuration();
            calculatePrices();
            return;
          }

          fetch(`get_options.php?service_id=${encodeURIComponent(serviceId)}`)
            .then((response) => response.json())
            .then((options) => {
              options.forEach((option) => {
                const opt = document.createElement('option');
                opt.value = option.option_id;
                opt.dataset.duration = option.duration;
                opt.dataset.price = option.price;
                opt.textContent = `${option.duration} minutes (€${option.price})`;
                optionSelect.appendChild(opt);
              });
              refreshTotalDuration();
              calculatePrices();
            })
            .catch(() => {
              /* Optionally handle error */
            });
        }

        // ---------- Time slots ----------
        function loadAvailableTimes(date) {
          const totalDuration = getTotalMinutes();
          const timeSelect = document.getElementById('startTime');
          timeSelect.innerHTML = '<option value="">Select a time</option>';

          if (!date || !totalDuration) {
            return;
          }

          fetch(
            `get_available_times.php?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(totalDuration)}`,
          )
            .then((response) => response.json())
            .then((times) => {
              times.forEach((time) => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                timeSelect.appendChild(option);
              });
              loadAvailableStaff();
            })
            .catch(() => {});
        }

        // ---------- Staff ----------
        function loadAvailableStaff() {
          const date = document.getElementById('bookingDate').value;
          const startTime = document.getElementById('startTime').value;
          const totalDuration = getTotalMinutes();
          const staffSelect = document.getElementById('staff');
          staffSelect.innerHTML = '<option value="">Select a staff member</option>';

          if (date && startTime && totalDuration) {
            fetch(
              `get_available_staff.php?date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(startTime)}&duration=${encodeURIComponent(totalDuration)}`,
            )
              .then((response) => response.json())
              .then((staffs) => {
                staffs.forEach((staff) => {
                  const option = document.createElement('option');
                  option.value = staff.staff_id;
                  option.textContent = staff.staff_name;
                  staffSelect.appendChild(option);
                });
              })
              .catch(() => {});
          }

          calculatePrices();
        }

        // ---------- Helpers for promotion timing ----------
        function getCurrentBookingMoment() {
          const now = new Date();
          const pad = (value) => value.toString().padStart(2, '0');
          return {
            date: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`,
            time: `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`,
          };
        }

        // ---------- Price summary ----------
        async function calculatePrices() {
          const priceTable = document.getElementById('priceTable');
          priceTable.innerHTML = '';

          let totalPrice = 0;
          let totalDiscount = 0;
          const optionIds = [];

          const rows = document.querySelectorAll('.service-row');
          rows.forEach((row) => {
            const optionSelect = row.querySelector('.option-select');
            const optionId = parseInt(optionSelect?.value || '0', 10);
            if (optionId > 0) {
              optionIds.push(optionId);
            }
          });

          let discountMap = {};
          const { date: bookingDate, time: bookingTime } = getCurrentBookingMoment();

          if (optionIds.length) {
            try {
              const response = await fetch('get_applicable_promotions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  option_ids: optionIds,
                  date: bookingDate,
                  time: bookingTime,
                }),
              });

              if (response.ok) {
                const data = await response.json();
                if (data && typeof data === 'object') {
                  discountMap = data.option_discounts || {};
                }
              }
            } catch (error) {
              console.warn('Failed to fetch promotions', error);
            }
          }

          document.querySelectorAll('.service-row').forEach((row) => {
            const serviceSelect = row.querySelector('.service-select');
            const optionSelect = row.querySelector('.option-select');

            const serviceName = serviceSelect?.options[serviceSelect.selectedIndex]?.text || '';
            const optionText = optionSelect?.options[optionSelect.selectedIndex]?.text || '';
            const price = parseFloat(optionSelect?.options[optionSelect.selectedIndex]?.dataset.price || 0);
            const optionId = parseInt(optionSelect?.value || '0', 10);

            if (serviceName && optionText && !Number.isNaN(price) && price > 0) {
              const discountInfo = discountMap[optionId] || null;
              const discountAmount = discountInfo
                ? parseFloat(discountInfo.discount_amount || 0)
                : 0;
              const finalPrice = discountInfo
                ? parseFloat(discountInfo.final_price || price - discountAmount)
                : price;

              totalPrice += price;
              totalDiscount += discountAmount;

              let priceCell = `€${price.toFixed(2)}`;
              if (discountAmount > 0) {
                priceCell = `€${finalPrice.toFixed(2)}<div class="text-muted small">ลด -€${discountAmount.toFixed(
                  2,
                )} จาก €${price.toFixed(2)}</div>`;
              }

              priceTable.insertAdjacentHTML(
                'beforeend',
                `
                  <tr>
                    <td>${serviceName}</td>
                    <td>${optionText}</td>
                    <td>${priceCell}</td>
                  </tr>
                `,
              );
            }
          });

          document.getElementById('totalPrice').textContent = `€${totalPrice.toFixed(2)}`;
          document.getElementById('discountAmount').textContent = `-€${totalDiscount.toFixed(2)}`;
          document.getElementById('finalPrice').textContent = `€${(totalPrice - totalDiscount).toFixed(2)}`;
        }
      </script>
    </main>
  </body>
</html>
