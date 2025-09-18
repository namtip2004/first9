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

  <style>
    .upload-box{
      border:2px dashed #ced4da; border-radius:8px; padding:16px; text-align:center; position:relative;
      min-height:150px; display:flex; align-items:center; justify-content:center; background:#fff;
    }
    .upload-box.dragover{ background:#f8f9fa; border-color:#0d6efd; }
    .upload-box input[type="file"]{ position:absolute; inset:0; opacity:0; cursor:pointer; }
    #previewImage{ display:none; max-width:100%; max-height:260px; }
    #uploadText{ color:#6c757d; }
  </style>
</head>

<body>
<?php $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", ""); ?>
<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">

  <div class="pagetitle">
    <h1>Booking System</h1>
    <nav><ol class="breadcrumb"></ol></nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">

            <form id="bookingForm" action="submit_booking.php" method="POST" enctype="multipart/form-data">

              <!-- Customer -->
              <div class="mb-3">
                <label for="customer" class="form-label">Select Customer</label>
                <select class="form-select" id="customer" name="customer_id" required style="width: 100%;">
                  <option value="">Select a customer</option>
                  <?php
                    $customers = $pdo->query("SELECT customer_id, customer_name FROM customer")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($customers as $customer) {
                      echo "<option value='{$customer['customer_id']}'>{$customer['customer_id']} - {$customer['customer_name']}</option>";
                    }
                  ?>
                </select>
              </div>

              <!-- Services -->
              <div id="servicesContainer">
                <div class="service-row card p-3 mb-3">
                  <div class="row">
                    <div class="col-md-6">
                      <label class="form-label">Select Service</label>
                      <select class="form-select service-select" name="services[]" onchange="loadOptions(this)">
                        <option value="">Select a service</option>
                        <?php
                          $services = $pdo->query("SELECT service_id, service_name FROM service WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
                          foreach ($services as $service) {
                            echo "<option value='{$service['service_id']}'>{$service['service_name']}</option>";
                          }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-5">
                      <label class="form-label">Select Option</label>
                      <select class="form-select option-select" name="options[]" onchange="onOptionChange(this)">
                        <option value="">Select an option</option>
                      </select>
                    </div>
                    <div class="col-md-1">
                      <button type="button" class="btn btn-danger mt-4" onclick="removeService(this)">X</button>
                    </div>
                  </div>
                </div>
              </div>

              <button type="button" class="btn btn-primary mb-3" onclick="addService()">Add Another Service</button>

              <!-- Total Duration -->
              <div class="mb-3">
                <label class="form-label">Total Duration</label>
                <input type="text" class="form-control" id="totalDuration" readonly>
              </div>

              <!-- Date & Time -->
              <div class="mb-3">
                <label for="bookingDate" class="form-label">Select Date</label>
                <input type="text" class="form-control" id="bookingDate" name="booking_date" required>
              </div>
              <div class="mb-3">
                <label for="startTime" class="form-label">Select Start Time</label>
                <select class="form-select" id="startTime" name="start_time" onchange="loadAvailableStaff()" required>
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
              <button type="submit" class="btn btn-primary mt-3">Confirm Booking</button>
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

    // ---------- Select2 for customer ----------
    document.addEventListener('DOMContentLoaded', function () {
      const customerSelect = document.getElementById('customer');
      if (customerSelect) {
        $(customerSelect).select2({ placeholder: "Search or select a customer", allowClear: true });
      }
    });

    // ---------- Flatpickr ----------
    flatpickr("#bookingDate", {
      dateFormat: "Y-m-d",
      minDate: "today",
      onChange: function(selectedDates, dateStr){ loadAvailableTimes(dateStr); }
    });

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
      document.getElementById('totalDuration').value = `${mins} minutes`;
      const date = document.getElementById('bookingDate').value;
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
      newRow.className = 'service-row card p-3 mb-3';
      newRow.innerHTML = `
        <div class="row">
          <div class="col-md-6">
            <label class="form-label">Select Service</label>
            <select class="form-select service-select" name="services[]" onchange="loadOptions(this)">
              <option value="">Select a service</option>
              <?php foreach ($services as $service) { ?>
                <option value="<?php echo $service['service_id']; ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Select Option</label>
            <select class="form-select option-select" name="options[]" onchange="onOptionChange(this)">
              <option value="">Select an option</option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-danger mt-4" onclick="removeService(this)">X</button>
          </div>
        </div>`;
      container.appendChild(newRow);
    }

    function removeService(btn){
      const row = btn.closest('.service-row');
      row.remove();
      refreshTotalDuration();
      calculatePrices();
    }

    // ---------- Load options for a given service (row-relative) ----------
    function loadOptions(serviceSelectEl){
      const row = serviceSelectEl.closest('.service-row');
      const optionSelect = row.querySelector('.option-select');
      const serviceId = serviceSelectEl.value;

      optionSelect.innerHTML = '<option value="">Select an option</option>';
      if (!serviceId){
        refreshTotalDuration();
        calculatePrices();
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
            opt.textContent = `${o.duration} minutes (€${o.price})`;
            optionSelect.appendChild(opt);
          });
          // หลังโหลดตัวเลือก เสนอให้ผู้ใช้เลือกเอง → แค่รีเฟรชสรุปเวลาราคา
          refreshTotalDuration();
          calculatePrices();
        })
        .catch(() => { /* เงียบไว้ก่อน หรือจะแจ้ง error ก็ได้ */ });
    }

    // ---------- Time slots ----------
    function loadAvailableTimes(date){
      const totalDuration = getTotalMinutes();
      const timeSelect = document.getElementById('startTime');
      timeSelect.innerHTML = '<option value="">Select a time</option>';

      if (!date || !totalDuration){ return; }

      fetch(`get_available_times.php?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(totalDuration)}`)
        .then(res => res.json())
        .then(times => {
          times.forEach(t => {
            const o = document.createElement('option');
            o.value = t; o.textContent = t;
            timeSelect.appendChild(o);
          });
          loadAvailableStaff();
        })
        .catch(() => {});
    }

    // ---------- Staff ----------
    function loadAvailableStaff(){
      const date = document.getElementById('bookingDate').value;
      const startTime = document.getElementById('startTime').value;
      const totalDuration = getTotalMinutes();
      const staffSelect = document.getElementById('staff');
      staffSelect.innerHTML = '<option value="">Select a staff member</option>';

      if (date && startTime && totalDuration){
        fetch(`get_available_staff.php?date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(startTime)}&duration=${encodeURIComponent(totalDuration)}`)
          .then(res => res.json())
          .then(staffs => {
            staffs.forEach(s => {
              const o = document.createElement('option');
              o.value = s.staff_id; o.textContent = s.staff_name;
              staffSelect.appendChild(o);
            });
          })
          .catch(() => {});
      }
    }

    // ---------- Price summary ----------
    function calculatePrices(){
      const priceTable = document.getElementById('priceTable');
      priceTable.innerHTML = '';
      let totalPrice = 0;

      document.querySelectorAll('.service-row').forEach(row => {
        const sSel = row.querySelector('.service-select');
        const oSel = row.querySelector('.option-select');

        const serviceName = sSel?.options[sSel.selectedIndex]?.text || '';
        const optionText  = oSel?.options[oSel.selectedIndex]?.text || '';
        const price = parseFloat(oSel?.options[oSel.selectedIndex]?.dataset.price || 0);

        if (serviceName && optionText && !isNaN(price) && price > 0){
          totalPrice += price;
          priceTable.insertAdjacentHTML('beforeend', `
            <tr>
              <td>${serviceName}</td>
              <td>${optionText}</td>
              <td>€${price.toFixed(2)}</td>
            </tr>
          `);
        }
      });

      document.getElementById('totalPrice').textContent = `€${totalPrice.toFixed(2)}`;
      document.getElementById('finalPrice').textContent = `€${totalPrice.toFixed(2)}`; // ยังไม่มีส่วนลด → final = total
    }
  </script>
</main>
</body>
</html>
