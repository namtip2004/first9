<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Pages / Login - NiceAdmin Bootstrap Template</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <main>
    <div class="container">

 <section class="section register d-flex flex-column justify-content-center align-items-center min-vh-100 py-4">
  <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

    <!-- Logo -->
    <a href="index.php" class="d-flex flex-column align-items-center mb-4">
      <img src="assets/img/Minimal-logo3.png" alt="" style="height: 130px;">
    </a>
    <!-- End Logo -->

    <div class="card mb-3 w-100 shadow-sm">
      <div class="card-body">
        
          <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
          <p class="text-center small">Enter your gmail & password to login</p>
       

        <form  class="row g-3  needs-validation" action="check_loginadmin.php" method="post" novalidate >

          <div class="col-12">
            <label for="yourUsername" class="form-label">gmail</label>
            <div class="input-group has-validation">
              <span class="input-group-text" id="inputGroupPrepend">@</span>
              <input type="text" name="username" class="form-control" id="yourUsername" required>
              <div class="invalid-feedback">Please enter your gmail.</div>
            </div>
          </div>

          <div class="col-12">
            <label for="yourPassword" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" id="yourPassword" required>
            <div class="invalid-feedback">Please enter your password!</div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
          </div>

          <div class="col-12">
            <button class="btn btn-primary w-100" type="submit">Login</button>
          </div>

          <div class="col-12">
            <p class="small mb-0">Don't have account? <a href="forms-layouts.php">Create an account</a></p>
          </div>
        </form>
      </div>
    </div>

    <!-- <div class="credits text-center">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
    </div> -->

  </div>
</section>


    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>