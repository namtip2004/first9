<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Contact Us</title>
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
                        <a class="nav-link" href="promotion.html">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Page -->
    <div id="contact" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Contact Us</h2>
            <p class="section-subtitle">ติดต่อเราเพื่อสอบถามข้อมูลเพิ่มเติม หรือจองบริการ</p>
            
            <div class="row">
                <div class="col-lg-8 mb-5">
                    <div class="contact-info">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Get in Touch</h4>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Location</h6>
                                <p>123 Sukhumvit Road, Watthana<br>Bangkok 10110, Thailand</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Phone</h6>
                                <p>+66 2 123 4567<br>+66 9 8765 4321</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Email</h6>
                                <p>info@pureserenityspa.com<br>booking@pureserenityspa.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Opening Hours</h6>
                                <p>Monday - Sunday: 9:00 AM - 9:00 PM<br>Public Holidays: 10:00 AM - 8:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="contact-info">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Quick Contact</h4>
                        <form id="contactForm" onsubmit="submitContact(event)">
                            <div class="mb-3">
                                <label for="contactName" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="contactName" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactPhone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="contactPhone" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="contactEmail">
                            </div>
                            <div class="mb-4">
                                <label for="contactMessage" class="form-label">Message *</label>
                                <textarea class="form-control" id="contactMessage" rows="4" required placeholder="สอบถามข้อมูล หรือข้อความ..."></textarea>
                            </div>
                            <button type="submit" class="btn-luxury w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>

                        <div id="contactSuccess" class="text-center" style="display: none; padding: 40px 20px;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 15px;"></i>
                            <h5 class="font-serif" style="color: var(--charcoal);">Message Sent!</h5>
                            <p style="color: var(--dark-gray); font-size: 0.9rem;">เราจะติดต่อกลับโดยเร็วที่สุด</p>
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