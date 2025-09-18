<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

require_once("connect_db.php");

$customer_id = $_SESSION['customer_id'];

// Fetch customer data
$sql = "SELECT * FROM customer WHERE customer_id = $customer_id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);


$sql1 = "
    SELECT 
        b.booking_id,
        b.booking_date,
        b.time_start,
        b.time_end,
        b.final_price,
        b.status,
        s.staff_name,
        GROUP_CONCAT(DISTINCT sv.service_name SEPARATOR ', ') as services,
        SUM(so.duration) as total_duration
    FROM booking b
    LEFT JOIN staff s ON b.staff_id = s.staff_id
    LEFT JOIN booking_seviceop bs ON b.booking_id = bs.booking_id
    LEFT JOIN service_option so ON bs.option_id = so.option_id
    LEFT JOIN service sv ON so.service_id = sv.service_id
    WHERE b.customer_id = $customer_id
    GROUP BY b.booking_id
    ORDER BY b.booking_date DESC, b.time_start DESC
    ";

    $result1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_assoc($result1);

// Function to format Thai date
function formatThaiDate($date) {
$english_months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $english_months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp); // Remove + 543 to use Gregorian year
    
    return "$day $month $year";
}

function getStatusBadge($status) {
    switch ($status) {
        case 'confirmed':
            return '<span class="badge bg-success">Confirmed</span>';
        case 'pending':
            return '<span class="badge bg-warning">Pending</span>';
        case 'completed':
            return '<span class="badge bg-primary">Completed</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Cancelled</span>';
        default:
            return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - First 9 Thai Massage</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>

    </style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include("navbar.php"); ?>

    <section class="profile-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="profile-container">
                        <!-- Profile Header -->
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?php if (!empty($row['profileimg'])): ?>
                                    <img src="/first9/admin/assets/img/<?php echo htmlspecialchars($row['profileimg']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <h2 class="profile-name font-display"><?= htmlspecialchars($row['customer_name']) ?></h2>
                            <p class="profile-email"><?= htmlspecialchars($row['gmail']) ?></p>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="profile-tabs">
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                        <i class="fas fa-user me-2"></i>Edit Profile
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                                        <i class="fas fa-calendar-alt me-2"></i>My Bookings
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="profileTabContent">
                            <!-- Profile Edit Tab -->
                            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                               

                                <form action="update_profile_user.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="customer_id" value="<?= $row['customer_id'] ?>">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label">Full Name</label>
                                                        <input type="text" name="customer_name" class="form-control" 
                                                               value="<?= htmlspecialchars($row['customer_name']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="gmail" class="form-control" 
                                                               value="<?= htmlspecialchars($row['gmail']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Phone</label>
                                                        <input type="tel" name="tel" class="form-control" 
                                                               value="<?= htmlspecialchars($row['tel']) ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Gender</label>
                                                        <select name="gender" class="form-control">
                                                            <option value="male" <?= $row['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                                            <option value="female" <?= $row['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                                            <option value="other" <?= $row['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Birthday</label>
                                                        <input type="date" name="birthday" class="form-control" 
                                                               value="<?= htmlspecialchars($row['birthday']) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Profile Image</label>
                                               <div class="form-group text-center">
        <label for="profileimg">
            <div style="cursor: pointer; display: inline-block; border: 2px dashed #ccc; padding: 10px; border-radius: 8px;">
            
            <img id="profile-preview" 
                     src="/first9/Admin/assets/img/<?= htmlspecialchars($row['profileimg'] ?? 'default.png') ?>?v=<?= time() ?>" 
                     alt="Profile" 
                     style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px;">
                <div style="font-size: 0.9em; color: #666; margin-top: 5px;">คลิกเพื่อเปลี่ยนรูป</div>
            </div>
        </label>

        <!-- ซ่อน input file -->
        <input type="file" id="profileimg" name="profileimg" accept="imag/*" style="display: none;" onchange="previewImage(event)">
    </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" name="update_profile" class="btn-update">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Bookings Tab -->
                            <div class="tab-pane fade" id="bookings" role="tabpanel">
                                <div class="section-header mb-4">
                                    <h3 class="section-title font-display" style="font-size: 2rem; margin-bottom: 10px;">My Booking History</h3>
                                    <!-- <p class="section-description">รายการจองบริการทั้งหมดของคุณ</p> -->
                                </div>

                                <?php if (empty($result1)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--luxury-gold); margin-bottom: 20px;"></i>
                                        <h4 class="font-display" style="color: var(--deep-brown); margin-bottom: 15px;">No Bookings Yet</h4>
                                        <p style="color: var(--deep-brown); margin-bottom: 30px;">You haven't made any bookings yet. Book your first spa treatment now!</p>
                                        <a href="booking.php" class="btn-luxury">
                                            <i class="fas fa-calendar-check me-2"></i>Book Now
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($result1 as $booking): ?>
                                        <div class="booking-card">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <div class="booking-date">
                                                        <i class="fas fa-calendar me-2"></i>
                                                        <?= formatThaiDate($booking['booking_date']) ?>
                                                        <span class="ms-3">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= date('H:i', strtotime($booking['time_start'])) ?> - <?= date('H:i', strtotime($booking['time_end'])) ?>
                                                        </span>
                                                    </div>
                                                    <div class="booking-details">
                                                        <p class="mb-2">
                                                            <i class="fas fa-spa me-2"></i>
                                                            <strong>Services:</strong> <?= htmlspecialchars($booking['services']) ?>
                                                        </p>
                                                        <?php if ($booking['staff_name']): ?>
                                                            <p class="mb-2">
                                                                <i class="fas fa-user-md me-2"></i>
                                                                <strong>Therapist:</strong> <?= htmlspecialchars($booking['staff_name']) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <p class="mb-2">
                                                            <i class="fas fa-clock me-2"></i>
                                                            <strong>Duration:</strong> <?= $booking['total_duration'] ?> minutes
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-md-end">
                                                    <div class="booking-price mb-2">
                                                        €<?= number_format($booking['final_price'], 0) ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <?= getStatusBadge($booking['status']) ?>
                                                    </div>
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Waiting for confirmation
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="text-center mt-4">
                                        <a href="booking.php" class="btn-luxury">
                                            <i class="fas fa-plus me-2"></i>Book Another Service
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
function previewImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById("profile-preview");
        preview.src = URL.createObjectURL(file);
    }
}


        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-custom');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>