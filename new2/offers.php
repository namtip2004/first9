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

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Exclusive Offers</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=open" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Include Navigation -->
    <?php include("navbar.php"); ?>

    <!-- OFFERS PAGE -->
    <div id="offers" class="page-section active" style="padding-top: 120px; background: white;">
       <div class="container">
    <div class="section-header fade-in">
        <div class="section-subtitle">Exclusive Offers</div>
        <h2 class="section-title font-display">Special Promotions</h2>
        <!-- <p class="section-description">
            Exclusive deals and valuable promotions for an unforgettable luxury experience.
        </p> -->
    </div>
    
    <?php if (empty($promotions)): ?>
    <div class="row">
        <div class="col-12 text-center">
            <div class="alert alert-info" style="background: var(--pearl-white); border: 1px solid rgba(201, 169, 110, 0.3); color: var(--deep-brown);">
                <i class="fas fa-info-circle me-2"></i>
                There are currently no active promotions. Please stay tuned for upcoming news from us.
            </div>
        </div>
    </div>
<?php else: ?>

                <div class="row">
                    <?php foreach ($promotions as $index => $promo): ?>
                        <div class="col-lg-4 mb-4 fade-in" style="animation-delay: <?php echo ($index + 1) * 0.1; ?>s;">
                            <div class="promotion-card">
                                <!-- <div class="promotion-badge">
                                    <?php echo getBadgeType($promo['discount'], $index); ?>
                                </div> -->
                                <h3 class="promotion-title font-display">
                                    <?php echo htmlspecialchars($promo['pm_name']); ?>
                                </h3>
                                <p class="promotion-description">
                                    <?php echo nl2br(htmlspecialchars($promo['description'])); ?>
                                </p>
                                
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
    <i class="fas fa-calendar-alt me-1"></i>
    Until <?php echo formatEnglishDate($promo['pm_end_date']); ?>
</div>


<a href="booking.php" class="btn btn-outline-light" role="button" aria-label="Book Now">
    <i class="fas fa-calendar-check me-1"></i>
    Book Now
</a>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Static Additional Offers (Customer Benefits) -->
            <!-- <div class="row mt-5">
                <div class="col-lg-6 mb-4 fade-in" style="animation-delay: 0.4s;">
                    <div style="background: var(--pearl-white); padding: 50px; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <h4 class="font-display mb-3" style="color: var(--charcoal);">
                            <i class="fas fa-birthday-cake me-2" style="color: var(--luxury-gold);"></i>
                            Birthday Celebration
                        </h4>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-bottom: 25px;">
                            ฉลองวันเกิดของคุณกับเรา รับส่วนลด 30% ในช่วงเดือนเกิด 
                            พร้อมเค้กวันเกิดและของขวัญพิเศษ (ต้องแสดงบัตรประชาชน)
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Discount:</span>
                                <span style="color: var(--deep-brown);"> 30% Off</span>
                            </div>
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Valid:</span>
                                <span style="color: var(--deep-brown);"> Birth Month</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 fade-in" style="animation-delay: 0.5s;">
                    <div style="background: var(--pearl-white); padding: 50px; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <h4 class="font-display mb-3" style="color: var(--charcoal);">
                            <i class="fas fa-user-friends me-2" style="color: var(--luxury-gold);"></i>
                            Referral Rewards
                        </h4>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-bottom: 25px;">
                            แนะนำเพื่อนมาใช้บริการ รับส่วนลด 20% สำหรับทั้งคุณและเพื่อน 
                            เมื่อเพื่อนจองและใช้บริการครั้งแรก
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Reward:</span>
                                <span style="color: var(--deep-brown);"> 20% Off Each</span>
                            </div>
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Limit:</span>
                                <span style="color: var(--deep-brown);"> Unlimited</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Promotion Management (Admin only) -->
            <?php if (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'admin'): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="text-center">
                            <a href="admin/promotion_management.php" class="btn btn-dark">
                                <i class="fas fa-cog me-1"></i>
                                จัดการโปรโมชั่น
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>
    
   
</body>
</html>