<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Promotions</title>
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
                        <a class="nav-link" href="booking.html">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="promotion.html">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Promotion Page -->
    <div id="promotion" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Special Promotions</h2>
            <p class="section-subtitle">โปรโมชั่นพิเศษและแพ็คเกจสุดคุ้มสำหรับคุณ</p>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Limited Time</div>
                        <h3 class="font-serif">First Visit Special</h3>
                        <p class="mb-4">สำหรับลูกค้าใหม่ รับส่วนลด 20% สำหรับการนวดครั้งแรก</p>
                        <div style="font-size: 2.5rem; color: var(--primary-gold); margin: 20px 0;">20% OFF</div>
                        <a href="booking.html" class="btn btn-outline-light">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Best Value</div>
                        <h3 class="font-serif">Couple Package</h3>
                        <p class="mb-4">แพ็คเกจคู่รัก นวดสำหรับ 2 ท่าน พร้อมเครื่องดื่มต้อนรับ</p>
                        <div style="font-size: 2rem; color: var(--primary-gold); margin: 20px 0;">฿2,500</div>
                        <small style="text-decoration: line-through; opacity: 0.7;">ราคาปกติ ฿3,200</small><br>
                        <a href="booking.html" class="btn btn-outline-light mt-2">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Premium</div>
                        <h3 class="font-serif">Monthly Membership</h3>
                        <p class="mb-4">สมาชิกรายเดือน นวด 4 ครั้ง พร้อมสิทธิประโยชน์พิเศษ</p>
                        <div style="font-size: 2rem; color: var(--primary-gold); margin: 20px 0;">฿2,800</div>
                        <small style="opacity: 0.8;">ประหยัด 30% ต่อเดือน</small><br>
                        <a href="contact.html" class="btn btn-outline-light mt-2">Learn More</a>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-6 mb-4">
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Birthday Special</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">ฉลองวันเกิดของคุณด้วยการนวดพิเศษ รับส่วนลด 25% ในช่วงเดือนเกิด (ต้องแสดงบัตรประชาชน)</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Valid:</span>
                            <span style="color: var(--dark-gray);">ตลอดเดือนเกิด</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Referral Rewards</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">แนะนำเพื่อนมาใช้บริการ รับส่วนลด 15% สำหรับทั้งคุณและเพื่อน เมื่อเพื่อนจองครั้งแรก</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Reward:</span>
                            <span style="color: var(--dark-gray);">15% Off สำหรับทั้งคู่</span>
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