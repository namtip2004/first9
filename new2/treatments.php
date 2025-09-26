<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Treatments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Include Navigation -->
    <?php include("navbar.php"); ?>

    <!-- TREATMENTS PAGE -->
    <div id="treatments" class="page-section active" style="padding-top: 120px; background: white;">
        <div class="container">
            <div class="section-header fade-in">
                <!-- <div class="section-subtitle">Premium Treatments</div> -->
                <h2 class="section-title font-display">Our Services</h2>
                <!-- <p class="section-description">
                    Choose the massage that best suits you from our curated collection of premium therapies.
                </p> -->
            </div>
            
            <div class="row">
                <!-- Royal Thai Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.1s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <img src="assets/img/woman-567021_1280.jpg">
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Royal Thai Massage</h3>
                            <p class="treatment-description">
                                A royal-inspired traditional Thai massage that blends acupressure, assisted stretching,
                                and energy balancing for profound relaxation.
                            </p>
                            <!-- <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>Improves flexibility and muscular strength.</li>
                                <li>Stimulates blood and lymphatic circulation.</li>
                                <li>Relieves stress and muscle tension effectively.</li>
                                <li>Rebalances the body’s energy pathways.</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">90 Minutes</span>
                                <span class="price-badge">€120</span>
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Aromatherapy Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.2s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <img src="assets/img/massage-therapy-1612308_1280.jpg">
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Signature Aromatherapy</h3>
                            <p class="treatment-description">
                                A bespoke aromatherapy ritual using curated essential oils from around the world,
                                combined with Swedish massage techniques for a luxurious, relaxing experience.
                            </p>
                            <!-- <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>Deep yet gentle muscle relaxation.</li>
                                <li>Nourishes and revitalises the skin.</li>
                                <li>Reduces stress through soothing aromatherapy.</li>
                                <li>Enhances sleep quality and overall wellbeing.</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">120 Minutes</span>
                                <span class="price-badge">€180</span>
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Hot Stone Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.3s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <img src="assets/img/essential-oils-1433692_1280.jpg">
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Volcanic Hot Stone Therapy</h3>
                            <p class="treatment-description">
                                Warmed volcanic stones melt away muscular tension effectively,
                                complemented by specialised massage techniques.
                            </p>
                            <!-- <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>Deep relief for tight, overworked muscles.</li>
                                <li>Boosts circulation and lymphatic flow.</li>
                                <li>Eases aches, pains, and inflammation.</li>
                                <li>Encourages a calm, grounded state of mind.</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">105 Minutes</span>
                                <span class="price-badge">€150</span>
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Herbal Compress -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.4s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <img src="assets/img/002c0b54b90189cee02881daf313fc92.jpg">
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Royal Herbal Compress</h3>
                            <p class="treatment-description">
                                A traditional Thai herbal compress featuring over 12 premium herbs,
                                delivering restorative benefits inspired by Thai medical wisdom.
                            </p>
                            <!-- <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>Crafted with 100% natural premium Thai herbs.</li>
                                <li>Reduces inflammation and swelling in muscles.</li>
                                <li>Supports recovery from strains and injuries.</li>
                                <li>Strengthens immunity and overall vitality.</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">120 Minutes</span>
                                <span class="price-badge">€220</span>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="text-center mt-5 fade-in" style="animation-delay: 0.5s;">
                <a href="booking.html" class="btn-luxury">
                    <i class="fas fa-calendar-check me-2"></i>Book Your Treatment Now
                </a>
            </div> -->
        </div>
    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>

</body>
</html>