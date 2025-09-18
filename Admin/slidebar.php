<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Start the session to access user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in and get their status (assuming 'status' is stored in session)
$isAdmin = isset($_SESSION['staff_level']) && $_SESSION['staff_level'] === 'admin';
$isAdmin2 = isset($_SESSION['staff_level']) && $_SESSION['staff_level'] === 'staff';
?>

<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <?php if ($isAdmin): ?>
      <!-- Show all menu items for Admin -->
      <!-- <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'index.php') echo ' active'; ?>" href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li> -->

      <li class="nav-item">       
          <a> <span>Data Management</span> </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_customer.php') echo ' active'; ?>" href="table_customer.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Customer Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_booking.php') echo ' active'; ?>" href="table_booking.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Booking Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_staff.php') echo ' active'; ?>" href="table_staff.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Staff Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_service.php') echo ' active'; ?>" href="table_service.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Service Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_tags.php') echo ' active'; ?>" href="table_tags.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Tags Data</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'table_promotion.php') echo ' active'; ?>" href="table_promotion.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Promotion Data</span>
        </a>
      </li>

      <li class="nav-item">       
          <a> <span>Reports</span> </a>
      </li>

            <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'report_income.php') echo ' active'; ?>" href="report_income.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>income</span>
        </a>
      </li>

            <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'report_booking.php') echo ' active'; ?>" href="report_booking.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Bookings</span>
        </a>
      </li>

            <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'report_customer.php') echo ' active'; ?>" href="report_customer.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Customer</span>
        </a>
      </li>
             
      <!-- <li class="nav-item">
        <a class="nav-link collapsed<?php if ($current_page == 'report_staff.php') echo ' active'; ?>" href="report_staff.php">
          <i class="bi bi-layout-text-window-reverse"></i>
          <span>Staff</span>
        </a>
      </li> -->



      
    <?php endif; ?>

<?php if ($isAdmin2): ?>
    <!-- Show Profile for all logged-in users (Admin and non-Admin) -->
    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'schedule.php') echo ' active'; ?>" href="schedule.php">
        <i class="bi bi-calendar-event"></i>
        <span>Schedule</span>
      </a>
    </li>

        <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'profile.php') echo ' active'; ?>" href="profile.php">
        <i class="bi bi-person"></i>
        <span>Profile</span>
      </a>
    </li>
<?php endif; ?>
  </ul>
</aside>