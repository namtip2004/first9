<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - สัมผัสความผ่อนคลายแท้จริง</title>
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
                        <a class="nav-link active" href="index.html">Home</a>
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
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Home Page -->
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-ornament">
                <i class="fas fa-spa"></i>
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="hero-content">
                            <h1 class="hero-title font-serif fade-in">
                                Pure <span class="hero-accent">Serenity</span><br>
                                Luxury Spa
                            </h1>
                            <p class="hero-subtitle fade-in" style="animation-delay: 0.2s;">
                                สัมผัสความผ่อนคลายแท้จริง ด้วยศิลปะการนวดไทยโบราณ 
                                ในบรรยากาศที่เงียบสงบ เพื่อความสมดุลของจิตใจและร่างกาย
                            </p>
                            <a href="booking.html" class="btn-luxury fade-in" style="animation-delay: 0.4s;">
                                <i class="fas fa-calendar-alt me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-5" style="padding: 120px 0 !important; background: white;">
            <div class="container">
                <h2 class="section-title font-serif fade-in">Our Services</h2>
                <p class="section-subtitle fade-in">บริการสปาระดับพรีเมียม ด้วยมือผู้เชี่ยวชาญและผลิตภัณฑ์คุณภาพสูง</p>
                
                <div class="row">
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-serif">นวดไทยโบราณ</h4>
                            <p>การนวดแบบไทยโบราณที่ช่วยคลายความตึงเครียดของกล้ามเนื้อ เพิ่มความยืดหยุ่นของร่างกาย และกระตุ้นการไหลเวียนโลหิต</p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h4 class="font-serif">นวดน้ำมันอโรม่า</h4>
                            <p>การนวดด้วยน้ำมันหอมระเหยธรรมชาติ ช่วยผ่อนคลายทั้งกายและใจ พร้อมบำรุงผิวให้นุ่มลื่น เรียบเนียน</p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="font-serif">สปาธรรมชาติ</h4>
                            <p>ผสมผสานสมุนไพรธรรมชาติและเทคนิคการดูแลแบบองค์รวม เพื่อความผ่อนคลายและสุขภาพที่ดีอย่างยั่งยืน</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 slide-in-left">
                        <h2 class="font-serif mb-4" style="color: var(--charcoal); font-size: 2.5rem; font-weight: 300;">About Our Sanctuary</h2>
                        <p class="lead mb-4" style="color: var(--dark-gray); line-height: 1.8;">Pure Serenity Spa เป็นสปาที่มุ่งมั่นในการให้บริการนวดและผ่อนคลายแบบองค์รวม ด้วยประสบการณ์กว่า 10 ปี ในการดูแลลูกค้าด้วยใจ</p>
                        <p style="color: var(--dark-gray); line-height: 1.8;">เราเชื่อว่าการผ่อนคลายที่แท้จริงเกิดจากความสมดุลระหว่างร่างกายและจิตใจ ด้วยการผสมผสานศิลปะการนวดไทยโบราณกับเทคนิคสมัยใหม่</p>
                    </div>
                    <div class="col-lg-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">10+</div>
                                    <div class="stats-label">Years Experience</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">1,000+</div>
                                    <div class="stats-label">Happy Clients</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">15+</div>
                                    <div class="stats-label">Treatments</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">98%</div>
                                    <div class="stats-label">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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