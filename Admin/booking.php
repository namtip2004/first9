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
    <!-- <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin-top: 20px; }
        .service-row { margin-bottom: 15px; }
        .summary-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .flatpickr-input { background-color: #fff; }
    </style> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
    <?php $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", ""); ?>
        <?php include("header.php"); ?>
    <?php include("slidebar.php"); ?>
    <main id="main" class="main pt-5 mt-5">
   
        <!-- <h1 class="text-center mb-4">Booking System</h1> -->

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

        <form id="bookingForm" action="submit_booking.php" method="POST" enctype="multipart/form-data">

<!-- Customer Selection -->
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


            <!-- Service and Option Selection -->
            <div id="servicesContainer">
                <div class="service-row card p-3 mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="service_1" class="form-label">Select Service</label>
                            <select class="form-select service-select" name="services[]" onchange="loadOptions(1)">
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
                            <label for="option_1" class="form-label">Select Option</label>
                            <select class="form-select option-select" name="options[]" onchange="calculateTotalDuration()">
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

            <!-- Date and Time Picker -->
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

            <!-- Staff Selection -->
            <div class="mb-3">
                <label for="staff" class="form-label">Select Staff</label>
                <select class="form-select" id="staff" name="staff_id" required>
                    <option value="">Select a staff member</option>
                </select>
            </div>

            <!-- Promotion Selection -->
            <div class="mb-3">
                <label for="promotion" class="form-label">Select Promotion</label>
                <select class="form-select" id="promotion" name="promotion_id" onchange="calculatePrices()">
                    <option value="">No Promotion</option>
                    <?php
                    $today = date('Y-m-d H:i:s');
                    $promotions = $pdo->query("
    SELECT DISTINCT p.promotion_id, p.pm_name, p.discount, p.apply_to_all 
    FROM promotion p
    LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
    WHERE 
        p.active = 1 
        AND p.pm_start_date <= '$today' 
        AND p.pm_end_date >= '$today'
        AND (p.apply_to_all = 1 OR ps.service_id IN (
            SELECT service_id FROM service WHERE is_active = 1
        ))
")->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($promotions as $promo) {
                        echo "<option value='{$promo['promotion_id']}' data-discount='{$promo['discount']}' data-apply-to-all='{$promo['apply_to_all']}'>{$promo['pm_name']} ({$promo['discount']}%)</option>";
                    }
                    ?>
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
                            <th>Discount (€)</th>
                            <th>Net Price (€)</th>
                        </tr>
                    </thead>
                    <tbody id="priceTable"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th id="totalPrice">0.00</th>
                            <th id="totalDiscount">0.00</th>
                            <th id="finalPrice">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>


                        <div class="col-md-12">
  <div class="upload-box" id="uploadBox">
    <div class="upload-text" id="uploadText">add evidence</div>
    <input type="file" id="imgprofile" name="imgprofile" />
    <img id="previewImage" alt="Preview" />
  </div>
</div>


            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary mt-3">Confirm Booking</button>
        </form>
    </div>
    </div>
    </div>
    </div>
    </section>

    <!-- Bootstrap 5 JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const uploadBox = document.getElementById('uploadBox');
  const fileInput = document.getElementById('imgprofile');
  const previewImage = document.getElementById('previewImage');
  const uploadText = document.getElementById('uploadText');

  uploadBox.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadBox.classList.add('dragover');
  });

  uploadBox.addEventListener('dragleave', () => {
    uploadBox.classList.remove('dragover');
  });

  uploadBox.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadBox.classList.remove('dragover');

    if (e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      showPreview(fileInput.files[0]);
    }
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      showPreview(fileInput.files[0]);
    }
  });
        let serviceCount = 1;

        function showPreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      previewImage.style.display = 'block';
      uploadText.style.display = 'none';
    }
    reader.readAsDataURL(file);
  }

        // Initialize Flatpickr
        flatpickr("#bookingDate", {
            dateFormat: "Y-m-d",
            minDate: "today",
            onChange: function(selectedDates, dateStr) {
                loadAvailableTimes(dateStr);
            }
        });

        // Add another service row
        function addService() {
            serviceCount++;
            const container = document.getElementById('servicesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'service-row card p-3 mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label for="service_${serviceCount}" class="form-label">Select Service</label>
                        <select class="form-select service-select" name="services[]" onchange="loadOptions(${serviceCount})">
                            <option value="">Select a service</option>
                            <?php foreach ($services as $service) { ?>
                                <option value="<?php echo $service['service_id']; ?>"><?php echo $service['service_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="option_${serviceCount}" class="form-label">Select Option</label>
                        <select class="form-select option-select" name="options[]" onchange="calculateTotalDuration()">
                            <option value="">Select an option</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger mt-4" onclick="removeService(this)">X</button>
                    </div>
                </div>`;
            container.appendChild(newRow);
        }

        // Remove a service row
        function removeService(button) {
            button.closest('.service-row').remove();
            calculateTotalDuration();
            calculatePrices();
            updatePromotionOptions();

        }

        // Load service options
        function loadOptions(index) {
            const select = document.querySelector(`#servicesContainer .service-row:nth-child(${index}) .service-select`);
            const optionSelect = document.querySelector(`#servicesContainer .service-row:nth-child(${index}) .option-select`);
            const serviceId = select.value;
            optionSelect.innerHTML = '<option value="">Select an option</option>';
            if (serviceId) {
                fetch(`get_options.php?service_id=${serviceId}`)
                    .then(response => response.json())
                    .then(options => {
                        options.forEach(option => {
                            optionSelect.innerHTML += `<option value="${option.option_id}" data-duration="${option.duration}" data-price="${option.price}">${option.duration} minutes (€${option.price})</option>`;
                        });
                        calculateTotalDuration();
                        calculatePrices();
                        updatePromotionOptions();

                    });
            } else {
                calculateTotalDuration();
                calculatePrices();
    

            }

        }

        // Calculate total duration
        function calculateTotalDuration() {
            let totalMinutes = 0;
            document.querySelectorAll('.option-select').forEach(select => {
                const duration = select.options[select.selectedIndex]?.dataset.duration || 0;
                totalMinutes += parseInt(duration);
            });
            document.getElementById('totalDuration').value = `${totalMinutes} minutes`;
            loadAvailableTimes(document.getElementById('bookingDate').value);
        }

        // Load available time slots
        function loadAvailableTimes(date) {
            const totalDuration = parseInt(document.getElementById('totalDuration').value) || 0;
            const timeSelect = document.getElementById('startTime');
            timeSelect.innerHTML = '<option value="">Select a time</option>';
            if (!date || !totalDuration) return;

            fetch(`get_available_times.php?date=${date}&duration=${totalDuration}`)
                .then(response => response.json())
                .then(times => {
                    times.forEach(time => {
                        timeSelect.innerHTML += `<option value="${time}">${time}</option>`;
                    });
                    loadAvailableStaff();
                });
        }

        // Load available staff
        function loadAvailableStaff() {
            const date = document.getElementById('bookingDate').value;
            const startTime = document.getElementById('startTime').value;
            const totalDuration = parseInt(document.getElementById('totalDuration').value) || 0;
            const staffSelect = document.getElementById('staff');
            staffSelect.innerHTML = '<option value="">Select a staff member</option>';

            if (date && startTime && totalDuration) {
                fetch(`get_available_staff.php?date=${date}&start_time=${startTime}&duration=${totalDuration}`)
                    .then(response => response.json())
                    .then(staff => {
                        staff.forEach(s => {
                            staffSelect.innerHTML += `<option value="${s.staff_id}">${s.staff_name}</option>`;
                        });
                    });
            }
        }

        // Calculate prices and discounts
        function calculatePrices() {
            const promotion = document.getElementById('promotion');
            const applyToAll = promotion.options[promotion.selectedIndex]?.dataset.applyToAll === '1';
            const discountPercent = parseFloat(promotion.options[promotion.selectedIndex]?.dataset.discount || 0);
            let totalPrice = 0, totalDiscount = 0;

            const priceTable = document.getElementById('priceTable');
            priceTable.innerHTML = '';

            document.querySelectorAll('.service-row').forEach(row => {
                const serviceSelect = row.querySelector('.service-select');
                const optionSelect = row.querySelector('.option-select');
                const serviceName = serviceSelect.options[serviceSelect.selectedIndex]?.text || '';
                const optionText = optionSelect.options[optionSelect.selectedIndex]?.text || '';
                const price = parseFloat(optionSelect.options[optionSelect.selectedIndex]?.dataset.price || 0);
                let discount = 0;

                if (applyToAll) {
                    discount = price * (discountPercent / 100);
                } else if (promotion.value) {
                    fetch(`check_promotion.php?promotion_id=${promotion.value}&service_id=${serviceSelect.value}`)
                        .then(response => response.json())
                        .then(applies => {
                            if (applies) discount = price * (discountPercent / 100);
                            updatePriceRow(serviceName, optionText, price, discount);
                        });
                    return;
                }

                updatePriceRow(serviceName, optionText, price, discount);
                totalPrice += price;
                totalDiscount += discount;
            });

            function updatePriceRow(serviceName, optionText, price, discount) {
                if (serviceName && optionText) {
                    const netPrice = price - discount;
                    priceTable.innerHTML += `
                        <tr>
                            <td>${serviceName}</td>
                            <td>${optionText}</td>
                            <td>€${price.toFixed(2)}</td>
                            <td>€${discount.toFixed(2)}</td>
                            <td>€${netPrice.toFixed(2)}</td>
                        </tr>`;
                    totalPrice += price;
                    totalDiscount += discount;
                    document.getElementById('totalPrice').textContent = `€${totalPrice.toFixed(2)}`;
                    document.getElementById('totalDiscount').textContent = `€${totalDiscount.toFixed(2)}`;
                    document.getElementById('finalPrice').textContent = `€${(totalPrice - totalDiscount).toFixed(2)}`;
                }
            }
        }

function updatePromotionOptions() {
    const selectedServiceIds = Array.from(document.querySelectorAll('.service-select'))
        .map(select => select.value)
        .filter(id => id); // Remove empty values

    const promotionSelect = document.getElementById('promotion');
    promotionSelect.innerHTML = '<option value="">Loading...</option>';

    if (selectedServiceIds.length === 0) {
        promotionSelect.innerHTML = '<option value="">No Promotion</option>';
        return;
    }

    const queryString = selectedServiceIds.join(',');

    fetch(`get_promotions.php?service_ids=${queryString}`)
        .then(res => res.json())
        .then(promotions => {
            promotionSelect.innerHTML = '<option value="">No Promotion</option>';
            promotions.forEach(promo => {
                const option = document.createElement('option');
                option.value = promo.promotion_id;
                option.dataset.discount = promo.discount;
                option.dataset.applyToAll = promo.apply_to_all;
                option.textContent = `${promo.pm_name} (${promo.discount}%)`;
                promotionSelect.appendChild(option);
            });
            calculatePrices();
        });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // เปิดการใช้งาน select2 สำหรับ customer dropdown
    document.addEventListener('DOMContentLoaded', function () {
        const customerSelect = document.getElementById('customer');
        if (customerSelect) {
            $(customerSelect).select2({
                placeholder: "Search or select a customer",
                allowClear: true
            });
        }
    });
</script>
    </main>
</body>
</html>