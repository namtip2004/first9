
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Luxury Wellness Sanctuary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<?php
    // Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "first9";

    try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch active promotions
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        COUNT(ps.service_id) as service_count,
        GROUP_CONCAT(s.service_name SEPARATOR ', ') as service_names
    FROM promotion p
    LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
    LEFT JOIN service s ON ps.service_id = s.service_id
    WHERE p.active = '1' 
    AND p.pm_start_date <= NOW() 
    AND p.pm_end_date >= NOW()
    GROUP BY p.promotion_id
    ORDER BY p.discount DESC
");
$stmt->execute();
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);


function formatEnglishDate($date) {
    $english_months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $english_months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);  // No +543 here, keep Gregorian year
    
    return "$day $month $year";
}


// Function to get badge type based on discount
function getBadgeType($discount, $index) {
    if ($discount >= 50) return 'Premium Offer';
    if ($discount >= 30) return 'Popular Deal';
    if ($index === 0) return 'Featured';
    return 'Special Offer';
}

// Function to get promotion price display
function getPriceDisplay($discount, $apply_to_all) {
    if ($apply_to_all) {
        return $discount . '% OFF';
    } else {
        return 'Up to ' . $discount . '% OFF';
    }
}
?>
</php>

<body>
    
    <!-- Include Navigation -->
      <?php include("navbar.php"); ?>

    <!-- HOME PAGE -->
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-xl-5">
                        <div class="hero-content">
                            <!-- <div class="hero-subtitle-small fade-in">Luxury Wellness Sanctuary</div> -->
                            <h1 class="hero-title font-display fade-in" style="animation-delay: 0.2s;">
                                First 9 <span class="hero-title-accent">thai Massage</span><br>
                                And Spa
                            </h1>
                            <p class="hero-description fade-in" style="animation-delay: 0.4s;">
Experience premium spa treatments with the art of traditional Thai massage
in a tranquil and luxurious setting, for true relaxation.
                            </p>
                            <!-- <a href="booking.php" class="btn-luxury fade-in" style="animation-delay: 0.6s;">
                                <i class="fas fa-calendar-check me-2"></i>Booking Now
                            </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>


<!-- Promotions Section -->

<?php if (empty($promotions)): ?>
<?php else: ?>
<section class="promotions-section">
    <div class="container">
        <div class="section-header fade-in">
            <div class="section-subtitle">Limited Time Offers</div>
            <h2 class="section-title font-display">Special Promotions</h2>
            <!-- <p class="section-description">
                Exclusive promotions for our valued customers to experience premium spa treatments.
            </p> -->
        </div>
        
            <div class="row g-4">
                <?php foreach ($promotions as $index => $promo): ?>
                    <div class="col-lg-4 col-md-4 fade-in" style="animation-delay: <?php echo 0.1 * ($index + 1); ?>s;">
                        <div class="promotion-card-home">
                            <!-- <div class="promo-badge"><?//php echo getBadgeType($promo['discount'], $index); ?></div> -->
                            <div class="promo-content">
                                <h3 class="font-display"><?php echo htmlspecialchars($promo['pm_name']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($promo['description'])); ?></p>
                                <?php if ($promo['service_count'] > 0 && !$promo['apply_to_all']): ?>
                                    <div class="mb-3" style="font-size: 0.9em; color: var(--luxury-gold);">
                                        <i class="fas fa-tags me-1"></i>
                                        Applicable to: <?php echo htmlspecialchars($promo['service_names']); ?>
                                    </div>
                                <?php elseif ($promo['apply_to_all']): ?>
                                    <div class="mb-3" style="font-size: 0.9em; color: var(--luxury-gold);">
                                        <i class="fas fa-star me-1"></i>
                                        Applicable to all services
                                    </div>
                                <?php endif; ?>
                                <div class="promotion-price font-display">
                                    <?php echo getPriceDisplay($promo['discount'], $promo['apply_to_all']); ?>
                                </div>
                                <div class="mt-3 mb-3" style="font-size: 0.85em; color: rgba(255,255,255,0.8);">
                                    <!-- <i class="fas fa-clock me-2"></i> -->
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Valid until <?php echo formatEnglishDate($promo['pm_end_date']); ?>
                                </div>
                                <a href="booking.php" class="btn btn-luxury mt-3">
                                    <i class="fas fa-calendar-check me-2"></i>Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Monthly Wellness Package -->
        <!-- <div class="row g-4 mt-4">
            <div class="col-lg-6 col-md-6 fade-in" style="animation-delay: 0.2s;">
                <div class="promotion-card-home">
                    <div class="promo-badge">PACKAGE DEAL</div>
                    <div class="promo-content">
                        <h3 class="font-display">Monthly Wellness Package</h3>
                        <p>4-session monthly package, 25% savings compared to individual bookings.</p>
                        <div class="promo-price">
                            <span class="old-price">7,200 THB</span>
                            <span class="new-price">5,400 THB</span>
                        </div>
                        <div class="promo-validity">
                            <i class="fas fa-infinity me-2"></i>
                            Permanent Offer
                        </div>
                        <a href="booking.php" class="btn btn-luxury mt-3">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                    </div>
                </div>
            </div>
            
            VIP Membership
            <div class="col-lg-6 col-md-6 fade-in" style="animation-delay: 0.3s;">
                <div class="promotion-card-home vip-promotion">
                    <div class="promo-badge vip-badge">
                        <i class="fas fa-crown"></i> VIP MEMBERSHIP
                    </div>
                    <div class="promo-content">
                        <h3 class="font-display">Pure Serenity VIP Club</h3>
                        <p>Join our VIP Club for exclusive benefits: 20% lifetime discount, priority booking, and special birthday gifts.</p>
                        <div class="vip-benefits">
                            <div class="benefit-item">
                                <i class="fas fa-percentage"></i>
                                <span>20% Lifetime Discount</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-calendar-star"></i>
                                <span>Priority Booking</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-gift"></i>
                                <span>Birthday Gift</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-phone"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                        <div class="promo-price">
                            <span>Membership Fee</span>
                            <strong>2,500 THB/Year</strong>
                        </div>
                        <a href="booking.php" class="btn btn-vip mt-3">
                            <i class="fas fa-crown me-2"></i>Join VIP Club
                        </a>
                    </div>
                </div>
            </div>
        </div> -->
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

<!-- เพิ่มหลัง About Section และก่อน </div> ของ HOME PAGE -->

        <!-- Owner Introduction Section -->
        <section class="owner-introduction-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 slide-in-left">
                        <div class="section-subtitle">Meet Our Founder</div>
                        <h2 class="section-title font-display">Vision Behind Pure Serenity</h2>
                        <p class="section-description" style="text-align: left; margin: 0;">
                            <strong>คุณสุดารัตน์ วงศ์เสรี</strong> ผู้ก่อตั้งและเจ้าของ Pure Serenity Spa 
                            ด้วยความหลงใหลในศิลปะการนวดไทยโบราณและการดูแลสุขภาพแบบองค์รวม 
                            จึงได้ก่อตั้งสปาแห่งนี้ขึ้นเพื่อมอบประสบการณ์การผ่อนคลายที่แท้จริงให้กับทุกคน
                        </p>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-top: 20px;">
                            ด้วยประสบการณ์กว่า 20 ปีในวงการสปาและการแพทย์แผนไทย รวมถึงการศึกษาดูงานจากสปาชั้นนำทั่วโลก 
                            ทำให้ Pure Serenity Spa เป็นสถานที่ที่ผสมผสานภูมิปัญญาไทยกับเทคนิคสมัยใหม่อย่างลงตัว
                        </p>
                        <div class="owner-mission">
                            <h4 class="font-display" style="color: var(--luxury-gold); margin-bottom: 15px;">พันธกิจของเรา</h4>
                            <p style="color: var(--deep-brown); line-height: 1.8; font-style: italic;">
                                "เรามุ่งมั่นที่จะเป็นสถานที่ที่ทุกคนสามารถหลีกหนีจากความเร่งรีบของชีวิต 
                                และค้นพบความสงบภายในใจผ่านศิลปะการนวดและการดูแลที่อบอุ่น"
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="owner-achievements">
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-award"></i>
                                </div>
                                <div class="achievement-content">
                                    <h5>การรับรองระดับสากล</h5>
                                    <p>ใบรับรองจากสถาบันนวดไทยและสมาคมสปาระดับโลก</p>
                                </div>
                            </div>
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="achievement-content">
                                    <h5>การศึกษาเฉพาะทาง</h5>
                                    <p>ปริญญาด้านการแพทย์แผนไทยและอาร์ยุรเวท</p>
                                </div>
                            </div>
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="achievement-content">
                                    <h5>ประสบการณ์สากล</h5>
                                    <p>ศึกษาดูงานสปาชั้นนำในบาหลี, ญี่ปุ่น และสวิตเซอร์แลนด์</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Our Professional Team</div>
                    <h2 class="section-title font-display">Expert Therapists</h2>
                    <p class="section-description">
                        ทีมนักบำบัดมืออาชีพที่ผ่านการคัดสรรและฝึกอบรมอย่างเข้มข้น เพื่อให้บริการในระดับมาตรฐานสูงสุด
                    </p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.1s;">
                        <div class="team-card">
                            <div class="team-image">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <div class="team-content">
                                <div class="team-badge">Head Therapist</div>
                                <h3 class="font-display">คุณปรียา สุขสวัสดิ์</h3>
                                <p class="team-description">
                                    นักนวดมืออาชีพที่มีประสบการณ์กว่า 15 ปี เชี่ยวชาญด้านการนวดแก้อาการปวด 
                                    และการนวดเพื่อสุขภาพ
                                </p>
                                <div class="team-expertise">
                                    <span>Deep Tissue Massage</span>
                                    <span>Reflexology</span>
                                    <span>Hot Stone Therapy</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.2s;">
                        <div class="team-card">
                            <div class="team-image">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="team-content">
                                <div class="team-badge">Aromatherapy Expert</div>
                                <h3 class="font-display">คุณมาลี จันทร์เพ็ญ</h3>
                                <p class="team-description">
                                    ผู้เชี่ยวชาญด้านอโรมาเธอราปีและการนวดด้วยน้ำมันหอมระเหย 
                                    มีประสบการณ์กว่า 12 ปี
                                </p>
                                <div class="team-expertise">
                                    <span>Aromatherapy</span>
                                    <span>Essential Oil Blend</span>
                                    <span>Relaxation Massage</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="team-card">
                            <div class="team-image">
                                <i class="fas fa-spa"></i>
                            </div>
                            <div class="team-content">
                                <div class="team-badge">Traditional Thai Massage</div>
                                <h3 class="font-display">คุณสมชาย ใสสะอาด</h3>
                                <p class="team-description">
                                    ผู้เชี่ยวชาญการนวดไทยโบราณแบบราชการ ผ่านการฝึกอบรมจากวัดโพธิ์ 
                                    มีประสบการณ์กว่า 18 ปี
                                </p>
                                <div class="team-expertise">
                                    <span>Royal Thai Massage</span>
                                    <span>Thai Herbal Compress</span>
                                    <span>Foot Massage</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hot Services Section -->
        <section class="hot-services-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Most Popular</div>
                    <h2 class="section-title font-display">Hot Services This Month</h2>
                    <p class="section-description">
                        บริการยอดนิยมที่ลูกค้าให้ความไว้วางใจและกลับมาใช้บริการอย่างต่อเนื่อง
                    </p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.1s;">
                        <div class="hot-service-card">
                            <div class="hot-badge">
                                <i class="fas fa-fire"></i>
                                HOT
                            </div>
                            <div class="service-image-hot">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-display">Royal Thai Massage</h4>
                            <p>การนวดไทยโบราณแบบราชการ 90 นาที</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(4.9/5)</span>
                            </div>
                            <div class="price-hot">1,800 บาท</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.2s;">
                        <div class="hot-service-card">
                            <div class="hot-badge">
                                <i class="fas fa-fire"></i>
                                HOT
                            </div>
                            <div class="service-image-hot">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="font-display">Aromatherapy Oil Massage</h4>
                            <p>นวดด้วยน้ำมันหอมระเหย 60 นาที</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(4.8/5)</span>
                            </div>
                            <div class="price-hot">1,500 บาท</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="hot-service-card">
                            <div class="hot-badge">
                                <i class="fas fa-fire"></i>
                                HOT
                            </div>
                            <div class="service-image-hot">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h4 class="font-display">Couple Spa Package</h4>
                            <p>แพ็คเกจสปาคู่รัก 120 นาที</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>(5.0/5)</span>
                            </div>
                            <div class="price-hot">3,200 บาท</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>

</body>
</html>