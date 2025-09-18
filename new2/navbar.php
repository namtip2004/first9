<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$profileImg = $_SESSION['profileimg'] ?? 'profile-img.jpg';
// ตรวจสอบหน้าเพจปัจจุบัน
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/Minimal-logo3.png" alt="Pure Serenity Logo" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo $current_page === 'treatments.php' ? 'active' : ''; ?>" href="treatments.php">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo $current_page === 'booking.php' ? 'active' : ''; ?>" href="booking.php">Booking</a>
                </li>


                
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
                </li>
                <?php if (!isset($_SESSION['customer_name']) || empty($_SESSION['customer_name'])): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="login.php">Login</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center <?php echo $current_page === 'users-profile.php' ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <img 
                                src="/first9/admin/assets/img/<?php echo htmlspecialchars($profileImg); ?>" 
                                alt="Profile" 
                                class="rounded-circle profile-img"
                            >
                            <span class="dropdown-toggle">
                                <?php echo !empty($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : 'User'; ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="profileuser.php">
                                    <i class="bi bi-person"></i>
                                    <span>My Profile</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>