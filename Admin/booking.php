<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Page</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    body {
      background-color: #f5f7fb;
    }

    .card h5.card-title {
      font-weight: 600;
    }

    .service-row {
      border: 1px dashed #ced4da;
      border-radius: 12px;
      background: rgba(13, 110, 253, 0.03);
    }

    .service-row .form-label {
      font-weight: 500;
    }

    .service-row .remove-service {
      margin-top: 0;
    }

    .compact-card .card-body {
      padding: 1.25rem;
    }

    .summary-card .table {
      font-size: 0.95rem;
    }

    .summary-card .table tbody td {
      vertical-align: middle;
    }

    .summary-card .table tfoot th {
      font-weight: 600;
    }

    .summary-card .table tfoot th:last-child {
      font-size: 1rem;
    }

    .upload-box {
      border: 2px dashed #ced4da;
      border-radius: 12px;
      padding: 16px;
      text-align: center;
      position: relative;
      min-height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      transition: all 0.2s ease-in-out;
    }

    .upload-box.dragover {
      background: #f1f5ff;
      border-color: #0d6efd;
    }

    .upload-box input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
    }

    #previewImage {
      display: none;
      max-width: 100%;
      max-height: 200px;
      object-fit: contain;
    }

    #uploadText {
      color: #6c757d;
      font-size: 0.85rem;
    }

    @media (max-width: 991.98px) {
      .sticky-lg-top {
        position: static !important;
      }
    }
  </style>
</head>

<body>
<?php $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", ""); ?>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>
<?php
  $customers = $pdo->query("SELECT customer_id, customer_name FROM customer ORDER BY customer_name")
                   ->fetchAll(PDO::FETCH_ASSOC);
  $services = $pdo->query("SELECT service_id, service_name FROM service WHERE is_active = 1 ORDER BY service_name")
                  ->fetchAll(PDO::FETCH_ASSOC);
?>

<main id="main" class="main pt-5 mt-5">

  <div class="pagetitle">
    <h1>Booking System</h1>
    <nav><ol class="breadcrumb"></ol></nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card border-0 shadow-none bg-transparent">
          <div class="card-body p-0">

            <form id="bookingForm" action="submit_booking.php" method="POST" enctype="multipart/form-data">
              <div class="row g-2">
                <div class="col-lg-8 d-flex flex-column gap-1">
                  <div class="card shadow-sm border-0 compact-card">
                    <div class="card-body">
                      <h5 class="card-title mb-2">Customer</h5>
                      <label for="customer" class="form-label">Select Customer</label>
                      <select class="form-select" id="customer" name="customer_id" required style="width: 100%;">
                        <option value="">Select a customer</option>
                        <?php foreach ($customers as $customer) { ?>
                          <option value="<?php echo htmlspecialchars($customer['customer_id']); ?>"><?php echo htmlspecialchars($customer['customer_id'] . ' - ' . $customer['customer_name']); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>

                  <div class="card shadow-sm border-0 compact-card">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <h5 class="card-title mb-0">Services</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addService()">Add Service</button>
                      </div>

                      <div id="servicesContainer" class="d-flex flex-column gap-2">
                        <div class="service-row p-2">
                          <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                              <label class="form-label">Select Service</label>
                              <select class="form-select service-select" name="services[]" onchange="loadOptions(this)">
                                <option value="">Select a service</option>
                                <?php foreach ($services as $service) { ?>
                                  <option value="<?php echo htmlspecialchars($service['service_id']); ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                            <div class="col-12 col-md-5">
                              <label class="form-label">Select Option</label>
                              <select class="form-select option-select" name="options[]" onchange="onOptionChange(this)">
                                <option value="">Select an option</option>
                              </select>
                            </div>
                            <div class="col-12 col-md-3 col-lg-2 col-xl-1">
                              <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center w-100 remove-service" onclick="removeService(this)" aria-label="Remove service">
                                <i class="bi bi-x-lg"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card shadow-sm border-0 compact-card">
                    <div class="card-body">
                      <h5 class="card-title mb-2">Schedule</h5>
                      <div class="row g-2">
                        <div class="col-sm-6">
                          <label for="bookingDate" class="form-label">Select Date</label>
                          <input type="text" class="form-control" id="bookingDate" name="booking_date" required disabled>
                        </div>
                        <div class="col-sm-6">
                          <label for="startTime" class="form-label">Select Start Time</label>
                          <select class="form-select" id="startTime" name="start_time" onchange="loadAvailableStaff()" required disabled>
                            <option value="">Select a time</option>
                          </select>
                        </div>
                        <div class="col-sm-6">
                          <label for="staff" class="form-label">Select Staff</label>
                          <select class="form-select" id="staff" name="staff_id" required disabled>
                            <option value="">Select a staff member</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-4">
                  <div class="d-flex flex-column gap-1 sticky-lg-top" style="top: 5.5rem;">
                    <div class="card shadow-sm summary-card border-0 compact-card">
                      <div class="card-body">
                        <h5 class="card-title mb-2">Price Summary</h5>
                        <div class="table-responsive">
                          <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                              <tr>
                                <th>Service</th>
                                <th>Option</th>
                                <th class="text-end">Price (€)</th>
                              </tr>
                            </thead>
                            <tbody id="priceTable">
                              <tr>
                                <td colspan="3" class="text-center text-muted py-4">Select service options to see pricing.</td>
                              </tr>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th colspan="2" class="text-muted text-uppercase small">Total Duration</th>
                                <th id="totalDurationDisplay" class="text-end text-muted small">0 minutes</th>
                              </tr>
                              <tr>
                                <th colspan="2">Total</th>
                                <th id="totalPrice" class="text-end">€0.00</th>
                              </tr>
                              <tr>
                                <th colspan="2">Discount</th>
                                <th id="discountAmount" class="text-end">-€0.00</th>
                              </tr>
                              <tr>
                                <th colspan="2">Final Price</th>
                                <th id="finalPrice" class="text-end">€0.00</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="card shadow-sm border-0 compact-card">
                      <div class="card-body">
                        <h5 class="card-title mb-2">Supporting Evidence</h5>
                        <div class="upload-box" id="uploadBox">
                          <div class="upload-text" id="uploadText">Add evidence (JPG, PNG, PDF)</div>
                          <input type="file" id="imgprofile" name="imgprofile" />
                          <img id="previewImage" alt="Preview" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary px-4 py-2 mt-2 mt-lg-0">Confirm Booking</button>
                </div>
              </div>
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
    const bookingDateInput = document.getElementById('bookingDate');
    const startTimeSelect = document.getElementById('startTime');
    const staffSelect = document.getElementById('staff');
    let bookingDatePicker;

    uploadBox.addEventListener('dragover', (e) => {
      e.preventDefault(); uploadBox.classList.add('dragover');
    });
    uploadBox.addEventListener('dragleave', () => uploadBox.classList.remove('dragover'));
    uploadBox.addEventListener('drop', (e) => {
      e.preventDefault(); uploadBox.classList.remove('dragover');
      if (e.dataTransfer.files.length > 0) { fileInput.files = e.dataTransfer.files; showPreview(fileInput.files[0]); }
    });
    fileInput.addEventListener('change', () => { if (fileInput.files.length > 0) showPreview(fileInput.files[0]); });
    function showPreview(file){
      const reader = new FileReader();
      reader.onload = e => { previewImage.src = e.target.result; previewImage.style.display='block'; uploadText.style.display='none'; };
      reader.readAsDataURL(file);
    }

    function parseNumber(value){
      if (value === undefined || value === null || value === '') {
        return null;
      }
      const num = Number.parseFloat(value);
      return Number.isNaN(num) ? null : num;
    }

    function formatCurrency(amount){
      if (typeof amount !== 'number' || Number.isNaN(amount)) {
        return '';
      }
      return `€${amount.toFixed(2)}`;
    }

    function applyStrikethrough(text){
      if (!text) {
        return '';
      }
      return `${text.split('').join('\u0336')}\u0336`;
    }

    function buildOptionLabel(durationValue, priceValue, discountInfo){
      const duration = Number.parseInt(durationValue ?? '', 10);
      const hasDuration = !Number.isNaN(duration) && duration > 0;
      const durationText = hasDuration ? `${duration} minutes` : '';
      const basePrice = parseNumber(priceValue);

      if (discountInfo && basePrice !== null){
        let finalPrice = parseNumber(discountInfo.final_price);
        const discountAmount = parseNumber(discountInfo.discount_amount);
        if (finalPrice === null && discountAmount !== null){
          finalPrice = Math.max(basePrice - discountAmount, 0);
        }
        if (finalPrice !== null && finalPrice < basePrice){
          const finalText = formatCurrency(finalPrice);
          const baseText = formatCurrency(basePrice);
          if (finalText && baseText){
            const baseStruck = applyStrikethrough(baseText);
            return durationText
              ? `${durationText} – ${finalText} (${baseStruck})`
              : `${finalText} (${baseStruck})`;
          }
        }
      }

      if (basePrice !== null && basePrice > 0){
        const baseText = formatCurrency(basePrice);
        if (baseText){
          return durationText ? `${durationText} – ${baseText}` : baseText;
        }
      }

      return durationText || 'Option';
    }

    function setOptionDisplay(option, discountInfo){
      const label = buildOptionLabel(option.dataset.duration, option.dataset.price, discountInfo);
      if (label){
        option.textContent = label;
      }

      const basePrice = parseNumber(option.dataset.price);
      let resolvedFinal = basePrice;
      let resolvedDiscount = 0;

      if (discountInfo && basePrice !== null){
        const fetchedFinal = parseNumber(discountInfo.final_price);
        const fetchedDiscount = parseNumber(discountInfo.discount_amount);
        if (fetchedFinal !== null && fetchedFinal < resolvedFinal){
          resolvedFinal = fetchedFinal;
        } else if (fetchedDiscount !== null && fetchedDiscount > 0){
          resolvedFinal = Math.max(basePrice - fetchedDiscount, 0);
        }
        if (resolvedFinal !== null && basePrice !== null){
          resolvedDiscount = Math.max(basePrice - resolvedFinal, 0);
        }
      }

      if (resolvedFinal !== null && typeof resolvedFinal === 'number'){
        option.dataset.finalPrice = resolvedFinal.toFixed(2);
      } else {
        option.dataset.finalPrice = '';
      }
      option.dataset.discountAmount = resolvedDiscount.toFixed(2);
    }

    async function applyOptionDiscountLabels(selectEl){
      if (!selectEl){ return; }

      const optionElements = Array.from(selectEl.options).filter(opt => Number.parseInt(opt.value || '0', 10) > 0);
      optionElements.forEach(opt => setOptionDisplay(opt, null));

      if (!optionElements.length){
        calculatePrices();
        return;
      }

      const optionIds = optionElements.map(opt => Number.parseInt(opt.value, 10));
      const { date, time } = getCurrentBookingMoment();

      try {
        const response = await fetch('get_applicable_promotions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ option_ids: optionIds, date, time })
        });

        if (response.ok){
          const data = await response.json();
          const discountMap = (data && typeof data === 'object') ? (data.option_discounts || {}) : {};
          optionElements.forEach(opt => {
            const optionId = Number.parseInt(opt.value, 10);
            setOptionDisplay(opt, discountMap[optionId] || null);
          });
        }
      } catch (error) {
        console.warn('Failed to load option discount details', error);
      } finally {
        calculatePrices();
      }
    }

    // ---------- Select2 for customer ----------
    document.addEventListener('DOMContentLoaded', function () {
      const customerSelect = document.getElementById('customer');
      if (customerSelect) {
        $(customerSelect).select2({ placeholder: "Search or select a customer", allowClear: true });
      }

      bookingDatePicker = flatpickr("#bookingDate", {
        dateFormat: "Y-m-d",
        minDate: "today",
        clickOpens: false,
        onChange: function(selectedDates, dateStr){
          loadAvailableTimes(dateStr);
          calculatePrices();
        }
      });

      updateScheduleAvailability(false);
    });

    function updateScheduleAvailability(hasDuration){
      if (!bookingDateInput || !startTimeSelect || !staffSelect) { return; }

      if (!hasDuration) {
        if (bookingDatePicker) {
          bookingDatePicker.clear();
          bookingDatePicker.set('clickOpens', false);
        }
        bookingDateInput.value = '';
        bookingDateInput.setAttribute('disabled', 'disabled');

        startTimeSelect.innerHTML = '<option value="">Select a time</option>';
        startTimeSelect.setAttribute('disabled', 'disabled');

        staffSelect.innerHTML = '<option value="">Select a staff member</option>';
        staffSelect.setAttribute('disabled', 'disabled');
      } else {
        bookingDateInput.removeAttribute('disabled');
        if (bookingDatePicker) {
          bookingDatePicker.set('clickOpens', true);
        }
      }
    }

    // ---------- Helpers ----------
    function getTotalMinutes(){
      let total = 0;
      document.querySelectorAll('.option-select').forEach(sel => {
        const dur = parseInt(sel.options[sel.selectedIndex]?.dataset.duration || 0);
        if (!isNaN(dur)) total += dur;
      });
      return total;
    }

    function refreshTotalDuration(){
      const mins = getTotalMinutes();
      const durationDisplay = document.getElementById('totalDurationDisplay');
      if (durationDisplay) {
        durationDisplay.textContent = `${mins} minutes`;
      }
      updateScheduleAvailability(mins > 0);
      const date = bookingDateInput ? bookingDateInput.value : '';
      if (date) loadAvailableTimes(date); // refresh slots when minutes change
    }

    function onOptionChange(optionEl){
      refreshTotalDuration();
      calculatePrices();
    }

    // ---------- Add / Remove service rows ----------
    function addService(){
      const container = document.getElementById('servicesContainer');
      const newRow = document.createElement('div');
      newRow.className = 'service-row p-2';
      newRow.innerHTML = `
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-6">
            <label class="form-label">Select Service</label>
            <select class="form-select service-select" name="services[]" onchange="loadOptions(this)">
              <option value="">Select a service</option>
              <?php foreach ($services as $service) { ?>
                <option value="<?php echo htmlspecialchars($service['service_id']); ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-12 col-md-5">
            <label class="form-label">Select Option</label>
            <select class="form-select option-select" name="options[]" onchange="onOptionChange(this)">
              <option value="">Select an option</option>
            </select>
          </div>
          <div class="col-12 col-md-3 col-lg-2 col-xl-1">
            <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center w-100 remove-service" onclick="removeService(this)" aria-label="Remove service">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>`;
      container.appendChild(newRow);
    }

    function removeService(btn){
      const container = document.getElementById('servicesContainer');
      const row = btn.closest('.service-row');
      row.remove();
      if (!container.querySelector('.service-row')) {
        addService();
      }
      refreshTotalDuration();
      calculatePrices();
    }

    // ---------- Load options for a given service (row-relative) ----------
    function loadOptions(serviceSelectEl){
      const row = serviceSelectEl.closest('.service-row');
      const optionSelect = row.querySelector('.option-select');
      const serviceId = serviceSelectEl.value;

      optionSelect.innerHTML = '<option value="">Select an option</option>';
      refreshTotalDuration();
      calculatePrices();
      if (!serviceId){
        return;
      }

      fetch(`get_options.php?service_id=${encodeURIComponent(serviceId)}`)
        .then(res => res.json())
        .then(options => {
          options.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.option_id;
            opt.dataset.duration = o.duration;
            opt.dataset.price = o.price;
            opt.textContent = buildOptionLabel(o.duration, o.price, null);
            optionSelect.appendChild(opt);
          });
          applyOptionDiscountLabels(optionSelect);
          // หลังโหลดตัวเลือก เสนอให้ผู้ใช้เลือกเอง → แค่รีเฟรชสรุปเวลาราคา
          refreshTotalDuration();
        })
        .catch(() => { /* เงียบไว้ก่อน หรือจะแจ้ง error ก็ได้ */ });
    }

    // ---------- Time slots ----------
    function loadAvailableTimes(date){
      const totalDuration = getTotalMinutes();

      if (!startTimeSelect || !staffSelect) { return; }

      startTimeSelect.innerHTML = '<option value="">Select a time</option>';
      startTimeSelect.setAttribute('disabled', 'disabled');

      staffSelect.innerHTML = '<option value="">Select a staff member</option>';
      staffSelect.setAttribute('disabled', 'disabled');

      if (!date || !totalDuration){ return; }

      fetch(`get_available_times.php?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(totalDuration)}`)
        .then(res => res.json())
        .then(times => {
          times.forEach(t => {
            const o = document.createElement('option');
            o.value = t; o.textContent = t;
            startTimeSelect.appendChild(o);
          });

          if (times.length) {
            startTimeSelect.removeAttribute('disabled');
          }

          loadAvailableStaff();
        })
        .catch(() => {});
    }

    // ---------- Staff ----------
    function loadAvailableStaff(){
      if (!staffSelect) { return; }

      const date = bookingDateInput ? bookingDateInput.value : '';
      const startTime = startTimeSelect ? startTimeSelect.value : '';
      const totalDuration = getTotalMinutes();
      staffSelect.innerHTML = '<option value="">Select a staff member</option>';
      staffSelect.setAttribute('disabled', 'disabled');

      if (date && startTime && totalDuration){
        fetch(`get_available_staff.php?date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(startTime)}&duration=${encodeURIComponent(totalDuration)}`)
          .then(res => res.json())
          .then(staffs => {
            staffs.forEach(s => {
              const o = document.createElement('option');
              o.value = s.staff_id; o.textContent = s.staff_name;
              staffSelect.appendChild(o);
            });

            if (staffs.length) {
              staffSelect.removeAttribute('disabled');
            }
          })
          .catch(() => {});
      }

      calculatePrices();
    }

    // ---------- Helpers for promotion timing ----------
    function getCurrentBookingMoment(){
      const now = new Date();
      const pad = (value) => value.toString().padStart(2, '0');
      return {
        date: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`,
        time: `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`,
      };
    }

    // ---------- Price summary ----------
    function calculatePrices(){
      const priceTable = document.getElementById('priceTable');
      if (!priceTable){ return; }

      priceTable.innerHTML = '';
      let totalBase = 0;
      let totalFinal = 0;
      let hasRows = false;

      document.querySelectorAll('.service-row').forEach(row => {
        const serviceSelect = row.querySelector('.service-select');
        const optionSelect = row.querySelector('.option-select');
        const selectedService = serviceSelect?.options[serviceSelect.selectedIndex];
        const selectedOption = optionSelect?.options[optionSelect.selectedIndex];

        if (!selectedService || !selectedOption){
          return;
        }

        const serviceName = selectedService.text || '';
        const optionId = Number.parseInt(selectedOption.value || '0', 10);
        if (!serviceName || optionId <= 0){
          return;
        }

        const basePrice = parseNumber(selectedOption.dataset.price);
        if (basePrice === null){
          return;
        }

        const finalPrice = parseNumber(selectedOption.dataset.finalPrice);
        const resolvedFinal = finalPrice !== null ? finalPrice : basePrice;
        const durationValue = Number.parseInt(selectedOption.dataset.duration || '', 10);
        const optionLabel = !Number.isNaN(durationValue) && durationValue > 0
          ? `${durationValue} minutes`
          : (selectedOption.textContent || '');

        totalBase += basePrice;
        totalFinal += resolvedFinal;

        let priceCell = `<span class="fw-semibold">€${basePrice.toFixed(2)}</span>`;
        if (resolvedFinal < basePrice){
          priceCell = `<span class="fw-semibold text-success d-block">€${resolvedFinal.toFixed(2)}</span><span class="text-muted text-decoration-line-through small d-block">€${basePrice.toFixed(2)}</span>`;
        }

        priceTable.insertAdjacentHTML('beforeend', `
          <tr>
            <td>${serviceName}</td>
            <td>${optionLabel}</td>
            <td class="text-end">${priceCell}</td>
          </tr>
        `);
        hasRows = true;
      });

      if (!hasRows){
        priceTable.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Select service options to see pricing.</td></tr>';
      }

      const totalDiscount = Math.max(totalBase - totalFinal, 0);
      document.getElementById('totalPrice').textContent = `€${totalBase.toFixed(2)}`;
      document.getElementById('discountAmount').textContent = `-€${totalDiscount.toFixed(2)}`;
      document.getElementById('finalPrice').textContent = `€${totalFinal.toFixed(2)}`;
    }
  </script>
</main>
</body>
</html>
