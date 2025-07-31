<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'index.php') echo 'active'; ?>" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'table_customer.php') echo 'active'; ?>" href="table_customer.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Customer Data Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'tables_member.php') echo 'active'; ?>" href="tables_member.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Booking Data Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'table_staff.php') echo 'active'; ?>" href="table_staff.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Staff Data Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'table_service.php') echo 'active'; ?>" href="table_service.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Service Data Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'table_tags.php') echo 'active'; ?>" href="table_tags.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Tags Data Management</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'tables_member.php') echo 'active'; ?>" href="tables_member.php">
        <i class="bi bi-layout-text-window-reverse"></i>
        <span>Promotion Data Management</span>
      </a>
    </li>

    <li class="nav-heading">Pages</li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'users-profile.php') echo 'active'; ?>" href="users-profile.php">
        <i class="bi bi-person"></i>
        <span>Profile</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'pages-contact.php') echo 'active'; ?>" href="pages-contact.php">
        <i class="bi bi-envelope"></i>
        <span>Contact</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed<?php if ($current_page == 'pages-login.php') echo 'active'; ?>" href="pages-login.php">
        <i class="bi bi-box-arrow-in-right"></i>
        <span>Login</span>
      </a>
    </li>

  </ul>

</aside>
