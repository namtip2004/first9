<?php
require_once 'connect_db.php';
$confirmedStatus = BOOKING_STATUS_CONFIRMED;
$completedStatus = BOOKING_STATUS_COMPLATE;

$hotServices = [];

if (isset($conn) && $conn instanceof mysqli) {
    $hotSql = "
        SELECT
            sv.service_id,
            sv.service_name,
            COALESCE(sv.description, '') AS description,
            COALESCE(sv.coverimg, '') AS coverimg,
            MIN(so.price) AS min_price,
            COUNT(DISTINCT b.booking_id) AS total_bookings
        FROM booking b
        JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
        JOIN service_option so ON bs.option_id = so.option_id
        JOIN service sv ON so.service_id = sv.service_id
        WHERE b.status IN ($confirmedStatus, $completedStatus)
        GROUP BY sv.service_id, sv.service_name, sv.description, sv.coverimg
        ORDER BY total_bookings DESC, sv.service_name ASC
        LIMIT 3
    ";

    if ($result = $conn->query($hotSql)) {
        while ($row = $result->fetch_assoc()) {
            $row['min_price'] = $row['min_price'] !== null ? (float) $row['min_price'] : null;
            $row['total_bookings'] = (int) ($row['total_bookings'] ?? 0);
            $hotServices[] = $row;
        }
        $result->free();
    }

    if (empty($hotServices)) {
        $fallbackSql = "
            SELECT
                s.service_id,
                s.service_name,
                COALESCE(s.description, '') AS description,
                COALESCE(s.coverimg, '') AS coverimg,
                (
                    SELECT MIN(so.price)
                    FROM service_option so
                    WHERE so.service_id = s.service_id
                ) AS min_price,
                0 AS total_bookings
            FROM service s
            WHERE s.is_active = 1
            ORDER BY s.service_name ASC
            LIMIT 3
        ";

        if ($fallbackResult = $conn->query($fallbackSql)) {
            while ($row = $fallbackResult->fetch_assoc()) {
                $row['min_price'] = $row['min_price'] !== null ? (float) $row['min_price'] : null;
                $row['total_bookings'] = (int) ($row['total_bookings'] ?? 0);
                $hotServices[] = $row;
            }
            $fallbackResult->free();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Luxury Wellness Sanctuary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>


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

                <!-- Hot Services Section -->
        <section class="hot-services-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Most Popular</div>
                    <h2 class="section-title font-display">Hot Services</h2>
                    <p class="section-description">
                        The treatments guests trust the most and return for again and again.
                    </p>
                </div>
                
                <div class="row g-4">
                    <?php if (!empty($hotServices)): ?>
                        <?php foreach ($hotServices as $index => $service): ?>
                            <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: <?= htmlspecialchars(number_format(0.1 + ($index * 0.1), 1)) ?>s;">
                                <div class="hot-service-card">
                                    <div class="hot-badge">
                                        <i class="fas fa-fire"></i>
                                        HOT
                                    </div>
                                    <div class="service-image-hot">
                                        <?php if (!empty($service['coverimg'])): ?>
                                            <img src="../Admin/assets/img/<?= htmlspecialchars($service['coverimg']) ?>" alt="<?= htmlspecialchars($service['service_name']) ?>">
                                        <?php else: ?>
                                            <i class="fas fa-spa"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="font-display"><?= htmlspecialchars($service['service_name']) ?></h4>
                                    <p><?= htmlspecialchars($service['description'] ?: 'Premium spa experiences designed for total relaxation') ?></p>
                                    <div class="rating">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>
                                            <?= $service['total_bookings'] > 0
                                                ? number_format($service['total_bookings']) . ' booking'
                                                : 'Ready to serve' ?>
                                        </span>
                                    </div>
                                    <div class="price-hot">
                                            <?= $service['min_price'] !== null
                                            ? 'Starting at €' . number_format($service['min_price'], 2)
                                            : 'Contact us for pricing' ?>
                                    </div>
                                </div>
                            </div>
              <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">Popular service data is currently unavailable.</p>
                        </div>
  <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <!-- <section class="services-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Our Signature Services</div>
                    <h2 class="section-title font-display">Luxury Wellness Treatments</h2>
                    <p class="section-description">
                        Premium spa rituals that fuse time-honoured Thai techniques with modern innovations
                        for an elevated relaxation experience.
                    </p>
                </div>


                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h3 class="font-display">Royal Thai Massage</h3>
                            <p>Royal-style traditional Thai massage using master-level techniques to balance body and mind for profound relaxation.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <h3 class="font-display">Aromatherapy Bliss</h3>
                            <p>Aromatic massage with natural essential oils from around the world, paired with specialised methods to nourish skin and soothe the senses.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h3 class="font-display">Luxury Holistic Spa</h3>
                            <p>Holistic care combining natural botanicals and contemporary spa science for lasting beauty and wellness.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->



        <!-- About Section -->
        <!-- <section class="about-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 slide-in-left">
                        <div class="section-subtitle">About Our Sanctuary</div>
                        <h2 class="section-title font-display">A Legacy of Luxury & Wellness</h2>
                        <p class="section-description" style="text-align: left; margin: 0;">
                            Pure Serenity Spa is recognised as a premium destination for holistic massage and wellness,
                            with more than 15 years of experience and a world-class team of specialists.
                        </p>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-top: 20px;">
                            We lead the way in blending the art of traditional Thai massage with contemporary spa innovations
                            to deliver a relaxation experience unlike any other.
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
        </section> -->

<!-- Added after the About Section and before the closing </div> of the HOME PAGE -->

        <!-- Owner Introduction Section -->
        <section class="owner-introduction-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 slide-in-left">
                        <div class="section-subtitle">Meet Our Founder</div>
                        <h2 class="section-title font-display">Vision Behind Pure Serenity</h2>
                        <p class="section-description" style="text-align: left; margin: 0;">
                            <strong>Khun Sudarat Wongseri</strong>, founder and owner of Pure Serenity Spa,
                            channelled her passion for traditional Thai massage and holistic wellbeing into creating this sanctuary.
                        </p>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-top: 20px;">
                            With over 20 years in the spa and Thai traditional medicine industries, along with immersive study tours at world-renowned spas,
                            she has shaped Pure Serenity Spa into a harmonious blend of Thai wisdom and modern innovation.
                        </p>
                        <div class="owner-mission">
                            <h4 class="font-display" style="color: var(--luxury-gold); margin-bottom: 15px;">Our Mission</h4>
                            <p style="color: var(--deep-brown); line-height: 1.8; font-style: italic;">
                                "We are devoted to providing a haven where everyone can pause from the rush of life
                                and discover inner peace through the art of massage and heartfelt care."
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
                                    <h5>International Accreditation</h5>
                                    <p>Certified by leading Thai massage institutes and global spa associations.</p>
                                </div>
                            </div>
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="achievement-content">
                                    <h5>Specialised Education</h5>
                                    <p>Holds degrees in Thai traditional medicine and Ayurveda.</p>
                                </div>
                            </div>
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="achievement-content">
                                    <h5>Global Experience</h5>
                                    <p>Completed immersive training with flagship spas in Bali, Japan, and Switzerland.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <!-- <section class="team-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Our Professional Team</div>
                    <h2 class="section-title font-display">Expert Therapists</h2>
                    <p class="section-description">
                        Our carefully selected therapists undergo extensive training to deliver service at the highest standard.
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
                                <h3 class="font-display">Khun Preeya Suksawat</h3>
                                <p class="team-description">
                                    A professional therapist with over 15 years of experience, specialising in therapeutic bodywork and wellness treatments.
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
                                <h3 class="font-display">Khun Malee Chanpen</h3>
                                <p class="team-description">
                                    Specialist in aromatherapy and essential oil massage techniques with more than 12 years of expertise.
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
                                <h3 class="font-display">Khun Somchai Saisa-ard</h3>
                                <p class="team-description">
                                    Master of royal traditional Thai massage, trained at Wat Pho with more than 18 years of practice.
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
        </section> -->



    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>

</body>
</html>