<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Booking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .booking-container {
            background: linear-gradient(135deg, var(--soft-cream), var(--warm-beige));
            min-height: 100vh;
            padding-top: 120px;
        }

        .services-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(139, 75, 92, 0.1);
            padding: 30px;
            height: fit-content;
            border-radius: 0px;
        }

        .cart-section {
            background: white;
            border-radius: 0px;
            box-shadow: 0 15px 50px rgba(139, 75, 92, 0.1);
            padding: 30px;
            position: sticky;
            top: 140px;
        }

        .service-card {
            background: var(--pearl-white);
            border: 1px solid rgba(201, 169, 110, 0.1);
            border-radius: 0px;
            padding: 25px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(139, 75, 92, 0.15);
            border-color: var(--luxury-gold);
        }

        .service-icon {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            border-radius: 0%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .option-item {
            background: rgba(201, 169, 110, 0.05);
            border: 1px solid rgba(201, 169, 110, 0.2);
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .option-item:hover {
            background: rgba(201, 169, 110, 0.1);
            border-color: var(--luxury-gold);
        }

        .option-item.selected {
            background: var(--luxury-gold);
            color: white;
            border-color: var(--deep-burgundy);
        }
        .option-item.promotion-active {
            border-color: rgba(201, 169, 110, 0.6);
            box-shadow: 0 8px 20px rgba(201, 169, 110, 0.25);
        }
        .option-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }
        .option-duration {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--deep-brown);
            font-weight: 600;
        }
        .option-pricing {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option-pricing .price-original {
            text-decoration: line-through;
            color: rgba(112, 85, 61, 0.55);
            font-size: 0.85rem;
        }
        .option-pricing .price-final,
        .option-pricing .option-price-normal {
            color: var(--luxury-gold);
            font-weight: 600;
            font-size: 1rem;
        }
        .price-original {
            text-decoration: line-through;
            color: rgba(112, 85, 61, 0.55);
            font-size: 0.9rem;
        }
        .price-final,
        .option-price-normal {
            color: var(--luxury-gold);
            font-weight: 600;
            font-size: 1rem;
        }
        .price-discount {
            color: var(--deep-burgundy);
            font-weight: 600;
        }
        .discount-ribbon {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--deep-burgundy);
            color: white;
            padding: 6px 14px;
            font-size: 0.75rem;
            font-weight: 700;
            border-bottom-left-radius: 12px;
            box-shadow: 0 6px 16px rgba(139, 75, 92, 0.25);
            z-index: 2;
            pointer-events: none;
        }
        .options-list {
            display: flex;
            flex-direction: row; /* Arrange options horizontally */
            flex-wrap: wrap; /* Wrap onto a new line if there are many choices */
            gap: 10px; /* Spacing between options */
        }

        .cart-item {
            background: var(--soft-cream);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--luxury-gold);
        }

        .staff-modal .modal-dialog {
            max-width: 800px;
        }

        .staff-card {
            background: white;
            border: 2px solid rgba(201, 169, 110, 0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .staff-card:hover, .staff-card.selected {
            border-color: var(--luxury-gold);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(139, 75, 92, 0.15);
        }

        .staff-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .calendar-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(139, 75, 92, 0.1);

        }

        .summary-card {
            background: linear-gradient(135deg, var(--charcoal), var(--deep-brown));
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        .summary-card .price-discount {
            color: #ffffffc1;
        }

    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php include("navbar.php"); ?>

    <div class="booking-container">
        <div class="container">
            <div class="section-header fade-in text-center mb-5">
                <!-- <div class="section-subtitle">Make a Reservation</div> -->
                <h2 class="section-title font-display">Booking</h2>
                <!-- <p class="section-description">
                    Reserve a premium spa journey and begin your path to total relaxation.
                </p> -->
            </div>

            <div class="row">
                <!-- Services Selection Section -->
                <div class="col-lg-7 mb-4">
                    <div class="services-section">
                        <h4 class="font-display mb-4" style="color: var(--charcoal);">
                            <i class="fas fa-spa me-2"></i>Choose Your Services
                        </h4>
                        <div id="servicesContainer">
                            <!-- Services will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Cart and Booking Details Section -->
                <div class="col-lg-5">
                    <div class="cart-section">
                        <div id="cartDisplaySection">
                        <h4 class="font-display mb-4" style="color: var(--charcoal);">
                            <i class="fas fa-shopping-cart me-2"></i>Selected Items
                        </h4>
                        
                        <div id="cartItems" class="mb-4">
                            <p class="text-center text-muted">No services selected yet.</p>
                        </div>
                        </div>

                        <!-- Customer Information -->
                        <!-- <div class="customer-info mb-4">
                            <h5 class="font-display mb-3" style="color: var(--deep-burgundy);">Customer Information</h5>
                            <?php if(isset($_SESSION['customer_id'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-user-check me-2"></i>
                                    Logged in as: <?php echo $_SESSION['customer_name'] ?? 'Customer'; ?>
                                </div> -->
                                <input type="hidden" id="customerId" value="<?php echo $_SESSION['customer_id']; ?>">
                            <!-- <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Please log in before booking.
                                </div>
                            <?php endif; ?>
                        </div> -->
        <div id="bookingFormSection">
           
                        <!-- Booking Form -->
                        <form id="bookingForm" method="POST" action="submit_booking.php">
                            <input type="hidden" name="customer_id" value="<?php echo $_SESSION['customer_id'] ?? ''; ?>">
                            <input type="hidden" name="services" id="selectedServices">
                            <input type="hidden" name="options" id="selectedOptions">
                            
                          <!-- Date Selection -->
<div class="form-group mb-3">
    <label class="form-label">
        <i class="fas fa-calendar me-1"></i>Select Date
    </label>
    <div class="d-flex justify-content-center">
    <div id="bookingDate" class="calendar-container">
    <input type="hidden" id="hiddenBookingDate" name="booking_date" required>
    </div>
    </div>
    
</div>

                            <!-- Time Selection -->
                            <div class="form-group mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock me-2"></i>Select Time
                                </label>
                                <select class="form-control" id="startTime" name="start_time" required disabled>
                                    <option value="">Select a time</option>
                                </select>
                                <div id="timeSlotMessage" class="form-text text-muted mt-1">
                                    Please choose services and a date to view available times.
                                </div>
                            </div>

                            <!-- Staff Selection -->
                            <div class="form-group mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user me-2"></i>Select a Service Provider
                                </label>
                                <button type="button" class="btn btn-outline-primary w-100" id="selectStaffBtn" data-bs-toggle="modal" data-bs-target="#staffModal">
                                    <i class="fas fa-users me-2"></i>Choose a Service Provider
                                </button>
                                <input type="hidden" name="staff_id" id="selectedStaffId">
                            </div>

                            <!-- Duration Display -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-hourglass-half me-2"></i>Total Duration
                                </label>
                                <input type="text" class="form-control" id="totalDuration" readonly>
                            </div>

                            <!-- Special Requests -->
                            <div class="form-group mb-3">
                                <label class="form-label">
                                    <i class="fas fa-comment me-2"></i>Additional Information
                                </label>
                                <textarea class="form-control" name="special_requests" rows="3" placeholder="Special requests or health information we should know."></textarea>
                            </div>

                            <!-- Price Summary -->
                            <div class="summary-card">
                                <h5 class="font-display mb-3">
                                    <i class="fas fa-receipt me-2"></i>Price Summary
                                </h5>
                                <div id="priceSummary">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total price:</span>
                                        <span id="totalPrice">€0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                                        <span>Discount:</span>
                                        <span id="totalDiscount" class="price-discount">-€0</span>
                                    </div>

                                    <hr style="border-color: rgba(255,255,255,0.3);">
                                    <div class="d-flex justify-content-between">
                                        <strong>Final price:</strong>
                                        <strong id="finalPrice" style="color: var(--luxury-gold);">€0</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
<button type="button" class="btn-luxury w-100 mt-4" id="proceedToPaymentBtn" disabled>
    <i class="fas fa-credit-card me-2"></i>Proceed to Payment
</button>
                        </form>
                        </div>

                        <div id="paymentSection" class="payment-section" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="font-display" style="color: var(--charcoal);">
            <i class="fas fa-credit-card me-2"></i>Payment
        </h4>
        <button type="button" class="btn btn-outline-secondary" id="backToBookingBtn">
            <i class="fas fa-arrow-left me-2"></i>Back
        </button>
    </div>

    <!-- Booking Summary -->
    <div class="booking-summary-card mb-4">
        <h5 class="font-display mb-3" style="color: var(--deep-burgundy);">
            <i class="fas fa-file-invoice me-2"></i>Booking Summary
        </h5>
        <div id="paymentSummaryDetails">
            <!-- Booking details will be rendered here -->
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="payment-methods-card mb-4">
        <h5 class="font-display mb-3" style="color: var(--deep-burgundy);">
            <i class="fas fa-money-check-alt me-2"></i>Payment Methods
        </h5>
        <div class="payment-methods">
            <div class="payment-method-item">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-university me-3" style="color: var(--luxury-gold); font-size: 1.5rem;"></i>
                    <strong>Bank Transfer</strong>
                </div>
                <div class="bank-details">
                    <p class="mb-1"><strong>Kasikorn Bank</strong></p>
                    <p class="mb-1">Account Name: Pure Serenity Spa</p>
                    <p class="mb-1">Account Number: 123-4-56789-0</p>
                </div>
            </div>
            
            <div class="payment-method-item">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-mobile-alt me-3" style="color: var(--luxury-gold); font-size: 1.5rem;"></i>
                    <strong>PromptPay</strong>
                </div>
                <div class="promptpay-details">
                    <p class="mb-1">Phone: 0xx-xxx-xxxx</p>
                    <p class="mb-1">or scan the QR code.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Proof Upload -->
    <div class="evidence-upload-card mb-4">
        <h5 class="font-display mb-3" style="color: var(--deep-burgundy);">
            <i class="fas fa-file-upload me-2"></i>Upload Payment Proof
        </h5>
        <div class="upload-box" id="evidenceUploadBox">
            <div class="upload-text" id="evidenceUploadText">Click or drag an image file here.</div>
            <input type="file" id="evidenceFile" name="evidence" accept="image/*" required />
            <img id="evidencePreviewImage" alt="Preview" style="display: none;" />
        </div>
    </div>

    <!-- Booking Confirmation Button -->
    <button type="button" class="btn-luxury w-100 mt-4" id="confirmBookingBtn">
        <i class="fas fa-check-circle me-2"></i>Confirm Booking
    </button>
</div>

                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Processing your booking...</p>
                        </div>

                        <!-- Success Message -->
                        <div id="bookingSuccess" class="success-message">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="success-title font-display">Booking Confirmed!</h3>
                            <p class="success-text">
                                Thank you for your reservation—your details have been received.<br>
                                Our team will contact you within two hours to confirm the appointment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Selection Modal -->
    <div class="modal fade staff-modal" id="staffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--luxury-gold); color: var(--charcoal);">
                    <h5 class="modal-title font-display">
                        <i class="fas fa-users me-2"></i>Select a Service Provider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="staffContainer" class="row">
                        <!-- Staff cards will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-luxury w-7" id="confirmStaffBtn" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        // Global variables
        let selectedItems = [];
        let selectedStaff = null;
        let totalDuration = 0;

        function formatCurrency(value) {
            const number = Number(value) || 0;
            return `€${number.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
        }

        function formatDiscount(value) {
            const number = Number(value) || 0;
            return `-€${number.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
        }

        function resolveDiscountPercent(percent, basePrice, finalPrice) {
            let resolved = Number(percent);
            if (!Number.isFinite(resolved) || resolved <= 0) {
                const base = Number(basePrice);
                const final = Number(finalPrice);
                if (Number.isFinite(base) && base > 0 && Number.isFinite(final)) {
                    const computed = ((base - final) / base) * 100;
                    resolved = computed > 0 ? computed : 0;
                } else {
                    resolved = 0;
                }
            }
            return resolved > 0 ? resolved : 0;
        }

        function formatDiscountPercentLabel(percent) {
            const value = Number(percent);
            if (!Number.isFinite(value) || value <= 0) {
                return '';
            }
            const rounded = Math.round(value * 10) / 10;
            const formatted = rounded % 1 === 0 ? rounded.toFixed(0) : rounded.toFixed(1);
            return `-${formatted}%`;
        }

        function getDiscountDisplayData(percent, basePrice, finalPrice) {
            const resolvedPercent = resolveDiscountPercent(percent, basePrice, finalPrice);
            return {
                percent: resolvedPercent,
                label: formatDiscountPercentLabel(resolvedPercent)
            };
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return value
                .toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getNowForPromotion() {
            const now = new Date();
            return {
                date: now.toISOString().slice(0, 10),
                time: now.toTimeString().slice(0, 8)
            };
        }

        function getPriceTotals() {
            return selectedItems.reduce((totals, item) => {
                const base = Number(item.originalPrice ?? item.price) || 0;
                const final = Number(item.price) || base;
                const discount = Number(item.discountAmount ?? Math.max(base - final, 0)) || 0;
                totals.original += base;
                totals.discount += Math.min(discount, base);
                totals.final += Math.max(final, 0);
                return totals;
            }, { original: 0, discount: 0, final: 0 });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadServices();
            initializeDatePicker();
            updateCartDisplay();
            
        });

        // Initialize Flatpickr for date selection
       function initializeDatePicker() {
    flatpickr("#bookingDate", {
        dateFormat: "Y-m-d",
        minDate: "today",
        maxDate: new Date().fp_incr(90), // 90 days from today
        locale: "en",
        inline: true, // Display the calendar inline
        onChange: function(selectedDates, dateStr) {
            if (dateStr) {
                document.getElementById('hiddenBookingDate').value = dateStr; // Update hidden input
                loadAvailableTimes(dateStr);
            }
        }
    });
}

        // Load services from database
        function loadServices() {
            fetch('get_services.php')
                .then(response => response.json())
                .then(services => {
                    const container = document.getElementById('servicesContainer');
                    container.innerHTML = '';
                    
                    services.forEach(service => {
                        const serviceCard = createServiceCard(service);
                        container.appendChild(serviceCard);
                        loadServiceOptions(service.service_id, service.service_name);
                    });
                })
                .catch(error => {
                    console.error('Error loading services:', error);
                    document.getElementById('servicesContainer').innerHTML =
                        '<p class="text-danger">An error occurred while loading services.</p>';
                });
        }

        // Create service card element
function createServiceCard(service) {
    const card = document.createElement('div');
    card.className = 'service-card';
    card.dataset.serviceId = service.service_id;
    card.dataset.serviceName = service.service_name;
    card.innerHTML = `
        <div class="d-flex align-items-start">
            <div class="service-icon me-3">
                <img
                    src="${service.coverimg ? '../admin/assets/img/' + service.coverimg : '../admin/assets/img/default.jpg'}"
                    alt="${service.service_name}" 
                    class="profile-img"
                    style="width: 140px; height: 140px; object-fit: cover;"
                >
            </div>
            <div class="flex-grow-1">
                <h5 class="font-display mb-2" style="color: var(--charcoal); text-align: left;">${service.service_name}</h5>
                <p class="mb-3" style="color: var(--deep-brown); text-align: left;">${service.description || 'Premium spa experiences'}</p>
                <div class="options-container">
                    <div id="options-${service.service_id}" class="options-list" style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 10px;">
                        <!-- Options will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    `;

    const optionsContainer = card.querySelector(`#options-${service.service_id}`);
    if (optionsContainer) {
        optionsContainer.innerHTML = '<small style="color: var(--deep-brown);">Loading options...</small>';
    }

    return card;
}

async function loadServiceOptions(serviceId, serviceName = '') {
    const container = document.getElementById(`options-${serviceId}`);
    if (!container) {
        return;
    }

    const resolvedServiceName = serviceName || container.closest('.service-card')?.dataset?.serviceName || '';

    try {
        const response = await fetch(`get_options.php?service_id=${serviceId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const options = await response.json();
        container.innerHTML = '';

        if (!Array.isArray(options) || options.length === 0) {
            container.innerHTML = '<small style="color: var(--deep-brown);">No options available</small>';
            return;
        }

        let promotionMap = {};
        try {
            const now = getNowForPromotion();
            const promoResponse = await fetch('get_applicable_promotions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    option_ids: options.map(opt => opt.option_id),
                    date: now.date,
                    time: now.time
                })
            });

            if (promoResponse.ok) {
                const promoData = await promoResponse.json();
                if (promoData && promoData.success) {
                    promotionMap = promoData.option_discounts || {};
                }
            }
        } catch (promoError) {
            console.warn('Error fetching promotions:', promoError);
        }

        options.forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.className = 'option-item';
            optionElement.style.backgroundColor = '#f8f9fa';
            optionElement.style.padding = '14px 18px';
            optionElement.style.borderRadius = '5px';
            optionElement.style.minWidth = '120px';
            optionElement.style.textAlign = 'left';
            optionElement.style.border = '1px solid #ddd';
            optionElement.style.cursor = 'pointer';

            const optionId = parseInt(option.option_id, 10);
            const basePrice = parseFloat(option.price) || 0;
            const durationMinutes = parseInt(option.duration, 10);
            const durationLabel = Number.isNaN(durationMinutes) ? `${option.duration} min` : `${durationMinutes} min`;
            const discountInfo = promotionMap[optionId] || null;

            let discountAmount = 0;
            let finalPrice = basePrice;
            let promotionName = '';
            let discountPercentRaw = 0;
            let promotionId = null;

            if (discountInfo && Number(discountInfo.discount_amount) > 0) {
                discountAmount = parseFloat(discountInfo.discount_amount) || 0;
                const finalFromApi = discountInfo.final_price !== undefined && discountInfo.final_price !== null
                    ? parseFloat(discountInfo.final_price)
                    : NaN;
                if (!Number.isNaN(finalFromApi)) {
                    finalPrice = finalFromApi;
                } else {
                    finalPrice = Math.max(basePrice - discountAmount, 0);
                }
                promotionName = discountInfo.promotion_name || '';
                discountPercentRaw = parseFloat(discountInfo.discount_percent || 0) || 0;
                promotionId = discountInfo.promotion_id ? parseInt(discountInfo.promotion_id, 10) : null;
            }

            const hasDiscount = discountAmount > 0 && finalPrice < basePrice;
            let resolvedDiscountPercent = 0;
            let discountLabel = '';
            if (hasDiscount) {
                const displayData = getDiscountDisplayData(discountPercentRaw, basePrice, finalPrice);
                resolvedDiscountPercent = displayData.percent;
                discountLabel = displayData.label;
                optionElement.classList.add('promotion-active');
                optionElement.style.paddingTop = '20px';
                optionElement.style.paddingRight = '78px';
            }

            const ribbonHtml = discountLabel ? `<div class="discount-ribbon">${discountLabel}</div>` : '';

            optionElement.innerHTML = `
                ${ribbonHtml}
                <div class="option-info">
                    <div class="option-duration"><i class="fas fa-clock"></i><span>${durationLabel}</span></div>
                    <div class="option-pricing">
                        ${hasDiscount
                            ? `<span class="price-original">${formatCurrency(basePrice)}</span>
                               <span class="price-final">${formatCurrency(finalPrice)}</span>`
                            : `<span class="option-price-normal">${formatCurrency(basePrice)}</span>`}
                    </div>
                </div>
            `;

            const enrichedOption = {
                option_id: optionId,
                duration: Number.isNaN(durationMinutes) ? 0 : durationMinutes,
                duration_label: durationLabel,
                base_price: basePrice,
                final_price: hasDiscount ? finalPrice : basePrice,
                discount_amount: hasDiscount ? discountAmount : 0,
                promotion_name: promotionName,
                promotion_id: promotionId,
                discount_percent: resolvedDiscountPercent,
                discount_label: discountLabel
            };

            optionElement.addEventListener('click', () => addToCart(serviceId, enrichedOption, resolvedServiceName));
            container.appendChild(optionElement);
        });
    } catch (error) {
        console.error('Error loading options:', error);
        container.innerHTML = '<small style="color: var(--deep-brown);">An error occurred while loading options.</small>';
    }
}

function addToCart(serviceId, option, serviceName = '') {
    const optionId = parseInt(option.option_id, 10);
    const durationMinutes = parseInt(option.duration, 10);
    const durationLabel = option.duration_label || (Number.isNaN(durationMinutes) ? `${option.duration} min` : `${durationMinutes} min`);
    const basePrice = typeof option.base_price !== 'undefined' ? parseFloat(option.base_price) : parseFloat(option.price);
    let finalPrice = typeof option.final_price !== 'undefined' ? parseFloat(option.final_price) : parseFloat(option.price);
    let discountAmount = typeof option.discount_amount !== 'undefined' ? parseFloat(option.discount_amount) : Math.max(basePrice - finalPrice, 0);

    if (Number.isNaN(finalPrice)) {
        finalPrice = basePrice;
    }
    if (Number.isNaN(discountAmount)) {
        discountAmount = Math.max(basePrice - finalPrice, 0);
    }

    discountAmount = Math.max(0, Math.min(discountAmount, basePrice));
    finalPrice = Math.max(0, finalPrice);

    const promotionName = option.promotion_name || '';
    const promotionId = option.promotion_id || null;
    const discountPercent = typeof option.discount_percent !== 'undefined' ? parseFloat(option.discount_percent) || 0 : 0;
    const discountLabelFromOption = option.discount_label || '';
    const discountDisplay = getDiscountDisplayData(discountPercent, basePrice, finalPrice);
    const resolvedDiscountPercent = discountDisplay.percent;
    const resolvedDiscountLabel = discountLabelFromOption || discountDisplay.label;

    const existingIndex = selectedItems.findIndex(item => item.serviceId === serviceId);
    const itemData = {
        serviceId: serviceId,
        serviceName: serviceName,
        optionId: optionId,
        duration: Number.isNaN(durationMinutes) ? 0 : durationMinutes,
        description: durationLabel,
        price: finalPrice,
        originalPrice: basePrice,
        discountAmount: discountAmount,
        promotionName: promotionName,
        promotionId: promotionId,
        discountPercent: resolvedDiscountPercent,
        discountLabel: resolvedDiscountLabel
    };

    if (existingIndex === -1) {
        selectedItems.push(itemData);
    } else {
        selectedItems[existingIndex] = itemData;
    }

    updateCartDisplay();
    updateTotalDuration();
    calculatePrices();
}

        // Remove item from cart
        function removeFromCart(index) {
            selectedItems.splice(index, 1);
            updateCartDisplay();
            updateTotalDuration();
            calculatePrices();
        }

        // Update cart display
// Replaces the original updateCartDisplay function
function updateCartDisplay() {
    const container = document.getElementById('cartItems');

    if (selectedItems.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No services selected yet.</p>';
        document.getElementById('proceedToPaymentBtn').disabled = true;
        return;
    }

    container.innerHTML = '';
    selectedItems.forEach((item, index) => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';

        const hasDiscount = Number(item.discountAmount) > 0;
        const serviceName = escapeHtml(item.serviceName || '');
        const description = escapeHtml(item.description || '');
        const priceSection = hasDiscount
            ? `
                <div class="price-original">${formatCurrency(item.originalPrice)}</div>
                <div class="price-final">${formatCurrency(item.price)}</div>
            `
            : `<div class="price-final">${formatCurrency(item.price)}</div>`;

        cartItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1" style="color: var(--charcoal);">${serviceName}</h6>
                    <small style="color: var(--deep-brown);">${description}</small>
                </div>
                <div class="text-end">
                    ${priceSection}
                    <button class="btn btn-sm btn-outline-danger mt-1" onclick="removeFromCart(${index})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(cartItem);
    });

    // Update hidden form fields
    document.getElementById('selectedServices').value = selectedItems.map(item => item.serviceId).join(',');
    document.getElementById('selectedOptions').value = selectedItems.map(item => item.optionId).join(',');

    // Enable payment button if customer is logged in
    document.getElementById('proceedToPaymentBtn').disabled = !document.getElementById('customerId').value;
}

        // Update total duration
        function updateTotalDuration() {
            totalDuration = selectedItems.reduce((sum, item) => sum + item.duration, 0);
            document.getElementById('totalDuration').value = `${totalDuration} minutes`;

            const timeSelect = document.getElementById('startTime');
            const timeMessage = document.getElementById('timeSlotMessage');

            if (totalDuration <= 0) {
                if (timeSelect) {
                    timeSelect.innerHTML = '<option value="">Select a time</option>';
                    timeSelect.disabled = true;
                }
                if (timeMessage) {
                    timeMessage.textContent = 'Please choose services and a date to view available times.';
                    timeMessage.classList.remove('text-danger');
                    timeMessage.classList.add('text-muted');
                }
                return;
            }

            // Reload available times if date is selected
            const selectedDate = document.getElementById('hiddenBookingDate').value;
            if (selectedDate) {
                loadAvailableTimes(selectedDate);
            } else {
                if (timeSelect) {
                    timeSelect.innerHTML = '<option value="">Select a time</option>';
                    timeSelect.disabled = true;
                }
                if (timeMessage) {
                    timeMessage.textContent = 'Please choose a date to view available times.';
                    timeMessage.classList.remove('text-danger');
                    timeMessage.classList.add('text-muted');
                }
            }
        }

        // Load available time slots
        function loadAvailableTimes(date) {
            const timeSelect = document.getElementById('startTime');
            const timeMessage = document.getElementById('timeSlotMessage');

            if (!totalDuration) {
                if (timeSelect) {
                    timeSelect.innerHTML = '<option value="">Select a time</option>';
                    timeSelect.disabled = true;
                }
                if (timeMessage) {
                    timeMessage.textContent = 'Please choose services and a date to view available times.';
                    timeMessage.classList.remove('text-danger');
                    timeMessage.classList.add('text-muted');
                }
                return;
            }

            if (timeSelect) {
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Loading...</option>';
            }
            if (timeMessage) {
                timeMessage.textContent = 'Searching for available times...';
                timeMessage.classList.remove('text-danger');
                timeMessage.classList.add('text-muted');
            }

            fetch(`get_available_times.php?date=${date}&duration=${totalDuration}`)
                .then(response => response.json())
                .then(data => {
                    if (!timeSelect) {
                        return;
                    }

                    const times = Array.isArray(data) ? data : [];
                    timeSelect.innerHTML = '';

                    if (!Array.isArray(data) && data && data.error) {
                        timeSelect.innerHTML = '<option value="">Unable to load times</option>';
                        timeSelect.disabled = true;
                        if (timeMessage) {
                            timeMessage.textContent = data.error;
                            timeMessage.classList.add('text-danger');
                            timeMessage.classList.remove('text-muted');
                        }
                        return;
                    }

                    if (times.length === 0) {
                        timeSelect.innerHTML = '<option value="">No times available</option>';
                        timeSelect.disabled = true;
                        if (timeMessage) {
                            timeMessage.textContent = 'No available times for the selected date or the spa is closed.';
                            timeMessage.classList.add('text-danger');
                            timeMessage.classList.remove('text-muted');
                        }
                        return;
                    }

                    timeSelect.innerHTML = '<option value="">Select a time</option>';
                    times.forEach(time => {
                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = time;
                        timeSelect.appendChild(option);
                    });

                    timeSelect.disabled = false;
                    timeSelect.onchange = loadAvailableStaff;

                    if (timeMessage) {
                        timeMessage.textContent = 'Select your preferred time.';
                        timeMessage.classList.remove('text-danger');
                        timeMessage.classList.add('text-muted');
                    }
                })
                .catch(error => {
                    console.error('Error loading times:', error);
                    if (timeSelect) {
                        timeSelect.innerHTML = '<option value="">Unable to load times</option>';
                        timeSelect.disabled = true;
                    }
                    if (timeMessage) {
                        timeMessage.textContent = 'Unable to load availability. Please try again.';
                        timeMessage.classList.add('text-danger');
                        timeMessage.classList.remove('text-muted');
                    }
                });
        }
        

        // Load available staff
        function loadAvailableStaff() {
            const date = document.getElementById('hiddenBookingDate').value;
            const startTime = document.getElementById('startTime').value;
            
            if (!date || !startTime || !totalDuration) return;
            
            fetch(`get_available_staff.php?date=${date}&start_time=${startTime}&duration=${totalDuration}`)
                .then(response => response.json())
                .then(staff => {
                    displayStaffOptions(staff);
                })
                .catch(error => console.error('Error loading staff:', error));
        }

        // Display staff options in modal
function displayStaffOptions(staff) {
  const container = document.getElementById('staffContainer');
  container.innerHTML = '';

  if (!Array.isArray(staff) || staff.length === 0) {
    container.innerHTML = '<div class="col-12 text-center text-muted">No service providers are available at this time.</div>';
    return;
  }

  staff.forEach(s => {
    const col = document.createElement('div');
    col.className = 'col-md-4 mb-3';
    col.innerHTML = `
      <div class="staff-card" data-id="${s.staff_id}" data-name="${s.staff_name}" data-img="${s.image_url || ''}">
        <div class="staff-avatar">
          ${s.image_url
            ? `<img src="${s.image_url}" alt="${s.staff_name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
            : `<i class="fas fa-user"></i>`}
        </div>
        <h6 class="mb-2" style="color: var(--charcoal);">${s.staff_name}</h6>
      </div>
    `;

    col.querySelector('.staff-card').addEventListener('click', function(){
      document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      selectedStaff = {
        id:   this.getAttribute('data-id'),
        name: this.getAttribute('data-name'),
        image:this.getAttribute('data-img')
      };
      document.getElementById('confirmStaffBtn').disabled = false;
    });

    container.appendChild(col);
  });
}

        // Select staff member
        function selectStaff(staffId, staffName, image) {
            // Remove previous selection
            document.querySelectorAll('.staff-card').forEach(card => card.classList.remove('selected'));
            
            // Add selection to clicked card
            event.currentTarget.classList.add('selected');
            
            selectedStaff = { id: staffId, name: staffName, image: image };
            document.getElementById('confirmStaffBtn').disabled = false;
        }

        // Confirm staff selection
        document.getElementById('confirmStaffBtn').addEventListener('click', function() {
            if (selectedStaff) {
                document.getElementById('selectedStaffId').value = selectedStaff.id;
                document.getElementById('selectStaffBtn').innerHTML = `
                    <i class="fas fa-user me-2"></i>${selectedStaff.name}
                `;
                document.getElementById('selectStaffBtn').classList.remove('btn-outline-primary');
                document.getElementById('selectStaffBtn').classList.add('btn-success');
            }
        });

        // Generate star rating HTML
        function generateStarRating(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<i class="fas fa-star" style="color: ${i <= rating ? '#ffc107' : '#e0e0e0'};"></i>`;
            }
            return stars;
        }

        // Calculate price summary with promotions
        function calculatePrices() {
            const totals = getPriceTotals();
            const totalPriceEl = document.getElementById('totalPrice');
            const totalDiscountEl = document.getElementById('totalDiscount');
            const discountRow = document.getElementById('discountRow');
            const finalPriceEl = document.getElementById('finalPrice');

            totalPriceEl.textContent = formatCurrency(totals.original);
            totalPriceEl.classList.remove('price-original');

            if (totals.discount > 0) {
                if (discountRow) {
                    discountRow.style.display = 'flex';
                }
                if (totalDiscountEl) {
                    totalDiscountEl.textContent = formatDiscount(totals.discount);
                }
            } else {
                if (discountRow) {
                    discountRow.style.display = 'none';
                }
                if (totalDiscountEl) {
                    totalDiscountEl.textContent = formatDiscount(0);
                }
            }

            finalPriceEl.textContent = formatCurrency(totals.final);
        }

        // Form submission
// Replaces the original form submission section
// Proceed to Payment
document.getElementById('proceedToPaymentBtn').addEventListener('click', function() {
    // Validate required fields
    if (selectedItems.length === 0) {
        showToast('Please select at least one service.', 'error');
        return;
    }
    
    if (!document.getElementById('hiddenBookingDate').value) {
        showToast('Please choose a date.', 'error');
        return;
    }
    
    if (!document.getElementById('startTime').value) {
        showToast('Please choose a time.', 'error');
        return;
    }
    
    if (!document.getElementById('selectedStaffId').value) {
        showToast('Please choose a service provider.', 'error');
        return;
    }
    
    if (!document.getElementById('customerId').value) {
        showToast('Please log in before booking.', 'error');
        return;
    }
    
    // Hide the selected items and booking form sections
    document.getElementById('cartDisplaySection').style.display = 'none';
    document.getElementById('bookingFormSection').style.display = 'none';
    document.getElementById('paymentSection').style.display = 'block';
    
    // Generate payment summary
    generatePaymentSummary();
});


// Back to booking
document.getElementById('backToBookingBtn').addEventListener('click', function() {
    document.getElementById('paymentSection').style.display = 'none';
    document.getElementById('cartDisplaySection').style.display = 'block';
    document.getElementById('bookingFormSection').style.display = 'block';
});

// Generate payment summary
function generatePaymentSummary() {
    const container = document.getElementById('paymentSummaryDetails');
    const date = document.getElementById('hiddenBookingDate').value;
    const time = document.getElementById('startTime').value;
    const staffName = selectedStaff ? selectedStaff.name : 'Not specified';

    // Calculate the end time from totalDuration (minutes)
    const startDate = time ? new Date(`2000-01-01T${time}:00`) : null;
    const endDate = startDate ? new Date(startDate.getTime() + (totalDuration || 0) * 60000) : null;
    const endTime = endDate
        ? `${String(endDate.getHours()).padStart(2,'0')}:${String(endDate.getMinutes()).padStart(2,'0')}`
        : '-';

    // Sum prices (supports scenarios with or without getPriceTotals())
    let totals;
    if (typeof getPriceTotals === 'function') {
        totals = getPriceTotals(); // { original, discount, final }
    } else {
        totals = selectedItems.reduce((acc, item) => {
            const base  = Number(item.originalPrice ?? item.base_price ?? item.price) || 0;
            const final = Number(item.price ?? item.final_price ?? base) || 0;
            const disc  = Math.max(base - final, 0);
            acc.original += base;
            acc.discount += disc;
            acc.final    += final;
            return acc;
        }, { original: 0, discount: 0, final: 0 });
    }

    let summaryHTML = `
        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Date - Time</div>
                <div class="summary-service-details">${date ? formatEnglishDate(date) : '-'}</div>
                <div class="summary-service-details">Time ${time || '-'} ${endDate ? `- ${endTime}` : ''}</div>
            </div>
        </div>

        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Therapist</div>
                <div class="summary-service-details">${escapeHtml(staffName)}</div>
            </div>
        </div>

        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Service</div>
                <div class="summary-subitem">
    `;

    // Display each service: name / time / price (showing both original and discounted prices when applicable)
    selectedItems.forEach(item => {
        const base  = Number(item.originalPrice ?? item.base_price ?? item.price) || 0;
        const final = Number(item.price ?? item.final_price ?? base) || 0;
        const hasDiscount = final < base;

        const priceHtml = hasDiscount
            ? `
                <div class="summary-price-block">
                    <div class="summary-price-original">${formatCurrency(base)}</div>
                    <div class="summary-price-final">${formatCurrency(final)}</div>
                </div>
              `
            : `<div class="summary-price">${formatCurrency(final)}</div>`;

        summaryHTML += `
            <div class="service-row">
                <div class="col-name">
                    <div class="summary-service-details">${escapeHtml(item.serviceName || '')}</div>
                </div>
                <div class="col-time">
                    <div class="summary-service-details">${escapeHtml(item.description || '')}</div>
                </div>
                <div class="col-price">
                    ${priceHtml}
                </div>
            </div>
        `;
    });

    summaryHTML += `
                </div>
            </div>
        </div>

        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Total Duration</div>
                <div class="summary-service-details">${totalDuration || 0} Min</div>
            </div>
        </div>

        <!-- Summary totals -->
        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Total</div>
            </div>
            <div class="summary-price">${formatCurrency(totals.original)}</div>
        </div>

        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Discount</div>
            </div>
            <div class="summary-price summary-price-discount">-${formatCurrency(totals.discount).replace('€','')}</div>
        </div>

        <div class="summary-item">
            <div class="summary-service">
                <div class="summary-service-name">Grand Total</div>
            </div>
            <div class="summary-price">${formatCurrency(totals.final)}</div>
        </div>
    `;

    container.innerHTML = summaryHTML;
}
function formatEnglishDate(dateString) {
    const date = new Date(dateString);
    const englishMonths = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const day = date.getDate();
    const month = englishMonths[date.getMonth()];
    const year = date.getFullYear();

    return `${day} ${month} ${year}`;
}

// Additional evidence upload helpers

// Evidence upload functionality - COMPLETE VERSION
const evidenceUploadBox = document.getElementById('evidenceUploadBox');
const evidenceFileInput = document.getElementById('evidenceFile');
const evidencePreviewImage = document.getElementById('evidencePreviewImage');
const evidenceUploadText = document.getElementById('evidenceUploadText');

// Click to select file
evidenceUploadBox.addEventListener('click', () => {
    evidenceFileInput.click();
});

// Drag and drop functionality
evidenceUploadBox.addEventListener('dragover', (e) => {
    e.preventDefault();
    evidenceUploadBox.classList.add('dragover');
});

evidenceUploadBox.addEventListener('dragleave', () => {
    evidenceUploadBox.classList.remove('dragover');
});

evidenceUploadBox.addEventListener('drop', (e) => {
    e.preventDefault();
    evidenceUploadBox.classList.remove('dragover');

    if (e.dataTransfer.files.length > 0) {
        evidenceFileInput.files = e.dataTransfer.files;
        showEvidencePreview(evidenceFileInput.files[0]);
    }
});

// File input change
evidenceFileInput.addEventListener('change', () => {
    if (evidenceFileInput.files.length > 0) {
        showEvidencePreview(evidenceFileInput.files[0]);
    }
});

// Show preview function
function showEvidencePreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
        evidencePreviewImage.src = e.target.result;
        evidencePreviewImage.style.display = 'block';
        evidenceUploadText.style.display = 'none';
    }
    reader.readAsDataURL(file);
}

// Final booking submission
document.getElementById('confirmBookingBtn').addEventListener('click', function() {
    // Validate evidence upload
    if (!evidenceFileInput.files.length) {
        showToast('Please attach proof of payment.', 'error');
        return;
    }
    
    // Show loading
    document.getElementById('paymentSection').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'block';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('customer_id', document.getElementById('customerId').value);
    formData.append('staff_id', document.getElementById('selectedStaffId').value);
    formData.append('booking_date', document.getElementById('hiddenBookingDate').value);
    formData.append('start_time', document.getElementById('startTime').value);
    formData.append('services', selectedItems.map(item => item.serviceId).join(','));
    formData.append('options', selectedItems.map(item => item.optionId).join(','));
    formData.append('special_requests', document.querySelector('textarea[name="special_requests"]').value);
    formData.append('evidence', evidenceFileInput.files[0]);
    
    // Submit form data
    fetch('submit_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingSpinner').style.display = 'none';
        
        if (data.success) {
            document.getElementById('bookingSuccess').style.display = 'block';
            
            // Reset form after success
            setTimeout(() => {
                location.reload();
            }, 5000);
        } else {
            document.getElementById('paymentSection').style.display = 'block';
            showToast(data.message || 'An error occurred while processing the booking.', 'error');
        }
    })
    .catch(error => {
        document.getElementById('loadingSpinner').style.display = 'none';
        document.getElementById('paymentSection').style.display = 'block';
        showToast('A connection error occurred.', 'error');
        console.error('Error:', error);
    });
});

        // Show toast notification
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} position-fixed`;
            toast.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

    </script>

    <!-- Additional PHP for getting services -->
    <?php
    // This would typically be in a separate file get_services.php
    if (isset($_GET['action']) && $_GET['action'] === 'get_services') {
        header('Content-Type: application/json');
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;dbname=first9", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $services = $pdo->query("
                SELECT service_id, service_name, description 
                FROM service 
                WHERE is_active = 1 
                ORDER BY service_name
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($services);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    ?>
    
    <!-- Styles for enhanced visual feedback -->
    <style>
        .option-item:hover {
            transform: translateX(10px);
        }
        
        .cart-item {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            transform: translateX(5px);
        }
        
        .staff-card.selected {
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            color: white;
        }
        
        .staff-card.selected .staff-avatar {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .flatpickr-calendar {
            border-radius: 15px !important;
            box-shadow: 0 15px 50px rgba(139, 75, 92, 0.15) !important;
        }
        
        .flatpickr-day.selected {
            background: var(--luxury-gold) !important;
            border-color: var(--luxury-gold) !important;
        }
        
        .flatpickr-day:hover {
            background: rgba(201, 169, 110, 0.1) !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .booking-container {
                padding-top: 100px;
            }
            
            .services-section, .cart-section {
                padding: 20px;
            }
            
            .service-card {
                padding: 20px;
            }
            
            .cart-section {
                position: relative;
                top: auto;
            }
            
            .staff-card {
                padding: 15px;
            }
            
            .staff-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }
        
        /* Animation for cart items */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .cart-item {
            animation: slideInRight 0.5s ease;
        }
        
        /* Loading animation */
        .loading-spinner .spinner {
            border: 3px solid rgba(201, 169, 110, 0.3);
            border-top: 3px solid var(--luxury-gold);
        }
        
        /* Success animation */
        .success-message {
            animation: fadeInUp 0.8s ease;
        }

        
        
        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

/* Additional styles appended at the end of the stylesheet */

/* Evidence Upload Styles */
.evidence-upload-card {
    background: var(--soft-cream);
    border: 2px solid var(--luxury-gold);
    border-radius: 15px;
    padding: 20px;
}

.upload-box {
    border: 2px dashed var(--luxury-gold);
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    position: relative;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.upload-box:hover {
    border-color: var(--deep-burgundy);
    background: var(--pearl-white);
}

.upload-box.dragover {
    border-color: var(--deep-burgundy);
    background: rgba(201, 169, 110, 0.1);
}

.upload-text {
    color: var(--deep-brown);
    font-weight: 500;
}

#evidenceFile {
    display: none;
}

/* Evidence Preview Image - optimal sizing */
#evidencePreviewImage {
    max-width: 200px;
    max-height: 200px;
    width: auto;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(139, 75, 92, 0.2);
    object-fit: cover;
}

/* Payment Methods Styles */
.payment-methods-card {
    background: var(--pearl-white);
    border: 1px solid rgba(201, 169, 110, 0.2);
    border-radius: 15px;
    padding: 20px;
}

.payment-method-item {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid var(--luxury-gold);
}

.bank-details, .promptpay-details {
    margin-left: 20px;
    color: var(--deep-brown);
}

/* Booking Summary Styles */
.booking-summary-card {
    background: white;
    border: 1px solid rgba(201, 169, 110, 0.2);
    border-radius: 15px;
    padding: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(201, 169, 110, 0.1);
}

.summary-item:last-child {
    border-bottom: 2px solid var(--luxury-gold);
    font-weight: bold;
    color: var(--deep-burgundy);
}

.summary-service-name {
    font-weight: 600;
    color: var(--charcoal);
}

.summary-service-details {
    color: var(--deep-brown);
    font-size: 0.9em;
}

.summary-price {
    font-weight: 600;
    color: var(--luxury-gold);
    text-align: right;
}

.summary-subitem {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Spacing between each row */
    width: 100%;
}

/* Three columns: name / duration / price */
.summary-subitem .service-row {
    display: grid;
    grid-template-columns: 1fr 80px 80px;
    align-items: center;
    gap: 8px;
}

.col-price {
    text-align: right;
    color: var(--luxury-gold);
    font-weight: 600;
}

/* Highlight price colour (example pastel accent) */
/* .summary-price { color: #e58b73; font-weight: 600; } */
/* To match the existing theme, keep the gold tone: */
.summary-price {
    color: var(--luxury-gold);
    font-weight: 600;
}

/* Price block showing both original and discounted values */
.summary-price-block {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

/* Original price (strikethrough, muted) */
.summary-price-original {
  text-decoration: line-through;
  opacity: 0.6;
  font-weight: 500;
}

/* Discounted price (highlighted in gold) */
.summary-price-final {
  color: var(--luxury-gold);
  font-weight: 700;
}

/* Discount colour within the summary */
.summary-price-discount {
  color: #e58b73;
  font-weight: 700;
}

/* Loading Spinner Styles */
.loading-spinner {
    display: none;
    text-align: center;
    padding: 50px;
    color: var(--deep-burgundy);
}

.spinner {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin: 0 auto 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Success Message Styles */
.success-message {
    display: none;
    text-align: center;
    padding: 50px;
    background: linear-gradient(135deg, var(--soft-cream), white);
    border-radius: 15px;
}

.success-icon {
    color: #28a745;
    font-size: 4rem;
    margin-bottom: 20px;
}

.success-title {
    color: var(--deep-burgundy);
    margin-bottom: 15px;
}

.success-text {
    color: var(--deep-brown);
    line-height: 1.6;
}
    </style>
</body>
</html>