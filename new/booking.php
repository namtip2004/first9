<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Book Appointment</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand font-serif" href="index.html">
                <i class="fas fa-leaf me-2"></i>Pure Serenity
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: none;">
                <i class="fas fa-bars" style="color: var(--primary-gold);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="massage.html">Massage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="booking.html">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="promotion.html">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Booking Page -->
    <div id="booking" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Book Appointment</h2>
            <p class="section-subtitle">จองการนวดที่เหมาะสมกับคุณ และเพลิดเพลินไปกับประสบการณ์ผ่อนคลาย</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="service-card">
                        <form id="bookingForm" onsubmit="submitBooking(event)">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="customerName" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="customerName" required placeholder="ชื่อ-นามสกุล">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="customerPhone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="customerPhone" required placeholder="เบอร์โทรศัพท์">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="customerEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="customerEmail" placeholder="อีเมล (ไม่บังคับ)">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="bookingDate" class="form-label">Preferred Date *</label>
                                    <input type="date" class="form-control" id="bookingDate" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="bookingTime" class="form-label">Preferred Time *</label>
                                    <select class="form-control" id="bookingTime" required>
                                        <option value="">เลือกเวลา</option>
                                        <option value="09:00">09:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="13:00">01:00 PM</option>
                                        <option value="14:00">02:00 PM</option>
                                        <option value="15:00">03:00 PM</option>
                                        <option value="16:00">04:00 PM</option>
                                        <option value="17:00">05:00 PM</option>
                                        <option value="18:00">06:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="massageType" class="form-label">Treatment Type *</label>
                                <select class="form-control" id="massageType" required onchange="updatePrice()">
                                    <option value="">เลือกประเภทการนวด</option>
                                    <option value="thai-800">นวดไทยโบราณ (60 นาที) - ฿800</option>
                                    <option value="aroma-1200">นวดน้ำมันอโรม่า (90 นาที) - ฿1,200</option>
                                    <option value="hotstone-1000">นวดหินร้อน (75 นาที) - ฿1,000</option>
                                    <option value="herbal-1500">นวดสมุนไพรไทย (90 นาที) - ฿1,500</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="specialRequest" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="specialRequest" rows="4" placeholder="ข้อมูลเพิ่มเติม หรือความต้องการพิเศษ (ไม่บังคับ)"></textarea>
                            </div>

                            <div id="priceDisplay" class="mb-4 p-4" style="background: var(--light-gray); border-left: 4px solid var(--primary-gold); display: none;">
                                <h5 class="font-serif" style="color: var(--charcoal);">Booking Summary</h5>
                                <div id="priceDetails"></div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-luxury">
                                    <i class="fas fa-check me-2"></i>Confirm Booking
                                </button>
                            </div>
                        </form>

                        <div id="bookingSuccess" class="text-center" style="display: none; padding: 40px;">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--primary-gold); margin-bottom: 20px;"></i>
                            <h3 class="font-serif" style="color: var(--charcoal); margin-bottom: 15px;">Booking Confirmed!</h3>
                            <p style="color: var(--dark-gray);">เราได้รับการจองของคุณแล้ว ทางเราจะติดต่อกลับเพื่อยืนยันนัดหมายในเร็วๆ นี้</p>
                        </div>

                        <div class="loading" id="loadingSpinner">
                            <div class="spinner"></div>
                            <p style="color: var(--dark-gray);">กำลังประมวลผล...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="font-serif">Pure Serenity Spa</h5>
                    <p style="line-height: 1.8; color: var(--dark-gray);">
                        ประสบการณ์การผ่อนคลายที่เหนือระดับ ด้วยศิลปะการนวดไทยโบราณ 
                        ในสถานที่ที่เงียบสงบและผ่อนคลาย
                    </p>
                    <div class="mt-4">
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-line"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 mb-4">
                    <h5>Services</h5>
                    <a href="massage.html">นวดไทยโบราณ</a>
                    <a href="massage.html">นวดอโรม่า</a>
                    <a href="massage.html">นวดหินร้อน</a>
                    <a href="massage.html">นวดสมุนไพร</a>
                </div>

                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <a href="index.html">Home</a>
                    <a href="massage.html">Massage</a>
                    <a href="booking.html">Booking</a>
                    <a href="promotion.html">Promotions</a>
                    <a href="contact.html">Contact</a>
                </div>

                <div class="col-lg-4 mb-4">
                    <h5>Contact Info</h5>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        123 Sukhumvit Road, Bangkok 10110
                    </div>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-phone" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        +66 2 123 4567
                    </div>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-envelope" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        info@pureserenityspa.com
                    </div>
                    <div>
                        <i class="fas fa-clock" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        Mon-Sun: 9:00 AM - 9:00 PM
                    </div>
                </div>
            </div>

            <div class="footer-divider"></div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Pure Serenity Spa. All rights reserved. | Designed with luxury in mind.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>