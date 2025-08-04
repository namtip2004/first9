<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>First9 Dashboard</title>

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Tagify -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
  <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

  <!-- Bootstrap 5.3 Bundle (JS + Popper) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/Minimal-logo3.png" alt="Logo">
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <!-- Profile Dropdown -->
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
<img 
  src="assets/img/<?php echo !empty($_SESSION['st_profile']) ? htmlspecialchars($_SESSION['st_profile']) : 'profile-img.jpg'; ?>" 
  alt="Profile" class="rounded-circle" 
  style="object-fit: cover; width: 40px; height: 40px;"
>
<span class="d-none d-md-block dropdown-toggle ps-2">
  <?php echo !empty($_SESSION['staff_name']) ? htmlspecialchars($_SESSION['staff_name']) : 'User'; ?>
</span>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <!-- <li class="dropdown-header">
              <h6><?php echo !empty($_SESSION['staff_name']) ? htmlspecialchars($_SESSION['staff_name']) : 'User'; ?></h6>
              <span><?php echo !empty($_SESSION['staff_level']) ? htmlspecialchars($_SESSION['staff_level']) : 'Staff'; ?></span>
            </li>
            <li><hr class="dropdown-divider"></li> -->

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
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

      </ul>
    </nav><!-- End Icons Navigation -->
  </header><!-- End Header -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>