<!DOCTYPE html>
<html lang="en">
<head>

</head>
<body>
<?php include 'navbar.php'; ?>
<main id="main" class="main">
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-floating-elements">
                <div class="floating-ornament"><i class="fas fa-leaf"></i></div>
                <div class="floating-ornament"><i class="fas fa-spa"></i></div>
                <div class="floating-ornament"><i class="fas fa-lotus"></i></div>
            </div>
            
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="hero-content">
                            <div class="hero-subtitle-small fade-in">Luxury Wellness Sanctuary</div>
                            <h1 class="hero-title font-display fade-in" style="animation-delay: 0.2s;">
                                Pure <span class="hero-title-accent">Serenity</span><br>
                                Spa Experience
                            </h1>
                            <p class="hero-description fade-in" style="animation-delay: 0.4s;">
                                สัมผัสประสบการณ์สปาระดับพรีเมียม ด้วยศิลปะการนวดไทยโบราณ 
                                ในสถานที่ที่เงียบสงบและหรูหรา เพื่อการผ่อนคลายอย่างแท้จริง
                            </p>
                            <a href="#" class="btn-luxury fade-in" style="animation-delay: 0.6s;" onclick="showPage('booking')">
                                <i class="fas fa-calendar-check me-2"></i>Reserve Your Experience
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="services-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Our Signature Services</div>
                    <h2 class="section-title font-display">Luxury Wellness Treatments</h2>
                    <p class="section-description">
                        บริการสปาระดับพรีเมียม ด้วยเทคนิคการนวดโบราณผสมผสานกับนวัตกรรมสมัยใหม่ 
                        เพื่อประสบการณ์การผ่อนคลายที่เหนือระดับ
                    </p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h3 class="font-display">Royal Thai Massage</h3>
                            <p>การนวดไทยโบราณแบบราชการ ด้วยเทคนิควิชาการระดับสูง ช่วยปรับสมดุลร่างกายและจิตใจ เพื่อความผ่อนคลายอย่างลึกซึ้ง</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <h3 class="font-display">Aromatherapy Bliss</h3>
                            <p>การนวดด้วยน้ำมันหอมระเหยธรรมชาติจากทั่วโลก ผสมผสานกับเทคนิคพิเศษ เพื่อการบำรุงผิวและผ่อนคลายทั้งกายและใจ</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h3 class="font-display">Luxury Holistic Spa</h3>
                            <p>การดูแลแบบองค์รวมด้วยสมุนไพรธรรมชาติและเทคนิคสมัยใหม่ เพื่อความงามและสุขภาพที่ดีอย่างยั่งยืน</p>
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
                        <div class="section-subtitle">About Our Sanctuary</div>
                        <h2 class="section-title font-display">A Legacy of Luxury & Wellness</h2>
                        <p class="section-description" style="text-align: left; margin: 0;">
                            Pure Serenity Spa ได้รับการยอมรับในฐานะสปาระดับพรีเมียม ที่ให้บริการการนวดและการดูแลสุขภาพแบบองค์รวม 
                            ด้วยประสบการณ์กว่า 15 ปี และทีมผู้เชี่ยวชาญระดับโลก
                        </p>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-top: 20px;">
                            เราเป็นผู้นำในการผสมผสานศิลปะการนวดไทยโบราณกับเทคนิคสปาสมัยใหม่ 
                            เพื่อมอบประสบการณ์การผ่อนคลายที่ไม่เหมือนใคร
                        </p>
                    </div>
                    
                    <div class="col-lg-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="stats-container">
                            <div class="stat-item">
                                <span class="stat-number font-display">15+</span>
                                <div class="stat-label">Years Excellence</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">5,000+</span>
                                <div class="stat-label">Happy Clients</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">25+</span>
                                <div class="stat-label">Luxury Treatments</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">99%</span>
                                <div class="stat-label">Satisfaction Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


</main>
<?//php include 'footerUser.php'; ?>
<!-- Include Footer -->
<?php include("footer.php"); ?>

</body>
</html>