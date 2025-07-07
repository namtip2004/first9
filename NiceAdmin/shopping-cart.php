<?php
    session_start();
    if(!isset($_SESSION['username'])){
      header("Location: index.html");
    }
  /*  if(!isset($_SESSION['username'])){
      header("Location:index.html");
    }*/
    require_once("dbcontroller.php");
    $db_handle = new DBController();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Forms / Layouts - NiceAdmin Bootstrap Template</title>
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

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="<?php 
    if (isset($_SESSION['level'])) {
        if ($_SESSION['level'] == 9) { 
            echo "index-admin.php";
        } elseif ($_SESSION['level'] == 1) {
            echo "index-user.php";
        } else {
            echo "pages-login.php";
        }
    } else {
        echo "pages-login.php";
    }
?>" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">kyoAdmin</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Lorem Ipsum</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Sit rerum fuga</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h4>Dicta reprehenderit</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
    <?php
    if (isset($_SESSION["customer_name"])) {
      // ถ้าผู้ใช้ล็อคอินแล้ว
      echo '<img src="assets/img/pro1.jpg" alt="Profile" class="rounded-circle">';
      echo '<span class="d-none d-md-block dropdown-toggle ps-2">' . htmlspecialchars($_SESSION["customer_name"]) . '</span>';
    } else {
      // ถ้าผู้ใช้ยังไม่ได้ล็อคอิน
      echo '<img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">';
      echo '<span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>';
    }
    ?>
  </a><!-- End Profile Image Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Kevin Anderson</h6>
              <span>Web Designer</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

              <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="<?php 
    if (isset($_SESSION['level'])) {
        if ($_SESSION['level'] == 9) { 
            echo "index-admin.php";
        } elseif ($_SESSION['level'] == 1) {
            echo "index-user.php";
        } else {
            echo "pages-login.php";
        }
    } else {
        echo "pages-login.php";
    }
?>">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-text"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 1) { ?>
            <!-- Do nothing for level 1 -->
        <?php } else { ?>
            <li>
                <a href="forms-layouts.php">
                    <i class="bi bi-circle"></i><span>ฟอร์มสมัครสมาชิก</span>
                </a>
            </li>
        <?php } ?>
        <li>
          <a href="shopping-cart.php">
            <i class="bi bi-circle"></i><span>สั่งสินค้า</span>
          </a>
        </li>
        <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 1) { ?>
            <!-- Do nothing for level 1 -->
        <?php } else { ?>
            <li>
            <a href="forms-product.php">
              <i class="bi bi-circle"></i><span>ฟอร์มสินค้า</span>
            </a>
          </li>
        <?php } ?>
        <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="forms-brand.php">
            <i class="bi bi-circle"></i><span>ฟอร์มแบรนด์</span>
        </a>
    </li>
<?php } ?>

        </ul>
      </li><!-- End Forms Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Tables</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="tables-data-customer-admin.php">
            <i class="bi bi-circle"></i><span>ข้อมูลลูกค้า</span>
        </a>
    </li>
<?php } else { ?>
    <li>
        <a href="tables-data-customer.php">
            <i class="bi bi-circle"></i><span>ข้อมูลลูกค้า</span>
        </a>
    </li>
<?php } ?>

<?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="tables-data-orders-admin.php">
            <i class="bi bi-circle"></i><span>ข้อมูลคำสั่งซื้อ</span>
        </a>
    </li>
<?php } else { ?>
    <li>
        <a href="tables-data-orders.php">
            <i class="bi bi-circle"></i><span>ข้อมูลคำสั่งซื้อ</span>
        </a>
    </li>
<?php } ?>

<?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="tables-orders-complete-admin.php">
            <i class="bi bi-circle"></i><span>รายการคำสั่งซื้อ</span>
        </a>
    </li>
<?php } else { ?>
    <li>
        <a href="tables-orders-complete.php">
            <i class="bi bi-circle"></i><span>ประวัติคำสั่งซื้อ</span>
        </a>
    </li>
<?php } ?>
<?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="tables-data-product-admin.php">
            <i class="bi bi-circle"></i><span>รายการสินค้า</span>
        </a>
    </li>
<?php } ?>
<?php if (isset($_SESSION['level']) && $_SESSION['level'] == 9) { ?>
    <li>
        <a href="tables-brand.php">
            <i class="bi bi-circle"></i><span>รายการแบรนด์</span>
        </a>
    </li>
<?php } ?>
</ul>
      </li><!-- End Tables Nav -->

      <li class="nav-heading">Pages</li>


      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-login.php">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Login</span>
        </a>
      </li><!-- End Login Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>ข้อมูลสินค้า</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?php 
    if (isset($_SESSION['level'])) {
        if ($_SESSION['level'] == 9) { 
            echo "index-admin.php";
        } elseif ($_SESSION['level'] == 1) {
            echo "index-user.php";
        } else {
            echo "pages-login.php";
        }
    } else {
        echo "pages-login.php";
    }
?>">Home</a></li>
          <li class="breadcrumb-item">Forms</li>
          <li class="breadcrumb-item active">Layouts</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

        <div class="card">
            <div class="card-body">

<!-- Shopping Cart -->
<HTML>
<HEAD>
<TITLE>Shopping Cart with Multi-Tab Wizard using jQuery AJAX</TITLE>
<link href="cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="./vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="./vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="./vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="./vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- Datatables -->
    
    <link href="./vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="./vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="./vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="./vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="./vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="./build/css/custom.min.css" rel="stylesheet">

<link href="style.css" type="text/css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
<script>
    
/* cart.js */
function cartAction(action,product_code) {
	var queryString = "";
	if(action != "") {
		switch(action) {
			case "add":
				queryString = 'action='+action+'&code='+ product_code+'&quantity='+$("#qty_"+product_code).val();
			break;
			case "remove":
				queryString = 'action='+action+'&code='+ product_code;
			break;
			case "empty":
				queryString = 'action='+action;
			break;
		}	 
	}
	jQuery.ajax({
	url: "ajax_action.php",
	data:queryString,
	type: "POST",
	success:function(data){
		$("#cart-item").html(data);
		if(action != "") {
			var cart_item_count = parseInt($("#cart-item-count").html());					
			switch(action) {
				case "add":
					$("#cart-item-count").html(cart_item_count+1);
					$("#add_"+product_code).hide();
					$("#added_"+product_code).show();
				break;
				case "remove":
					$("#cart-item-count").html(cart_item_count-1);
					
				break;
				case "empty":
					$("#cart-item-count").html(0);					
					$(".btnAddAction").show();
					$(".btnAdded").hide();
				break;
			}	 
		}
	},
	error:function (){}
	});
}
$(document).ready(function () {
	cartAction('','');	
	$("li").on("click", function() {
		$("li").removeClass("highlight");
		$(this).addClass("highlight");
		$(".content").hide();
		var id = $(this).attr("id");
		$("#"+id+"-grid").show();
	});
})</script>

<style>
        body{width:100%;}
</style>
</HEAD>
<BODY>
<ul id="cart-tab">
<li id="product" class="highlight">Products</li>
<li id="cart">Cart <span id="cart-item-count"><?php if(!empty($_SESSION["cart_item"])){ echo count($_SESSION["cart_item"]);} else{ echo "0"; } ?></span></li>
</ul>
<div class="clear-float"></div>
<div class="content" id="product-grid">
	<?php
	$product_array = $db_handle->runQuery("SELECT * FROM products ORDER BY id ASC");
	if (!empty($product_array)) { 
		foreach($product_array as $key=>$value){
	?>
		<div class="product-item">
			<form id="frmCart">
			<div class="product-image"><img src="assets/img/<?php echo $product_array[$key]["image"]; ?>" height="100" width="100"></div>
			<div><strong><?php echo $product_array[$key]["name"]; ?></strong></div>
			<div class="product-price"><?php echo "$".$product_array[$key]["price"]; ?></div>
			<div><input type="text" class="quantity" id="qty_<?php echo $product_array[$key]["code"]; ?>" name="quantity" value="1" size="2" />
			<?php
				$in_session = "0";
				if(!empty($_SESSION["cart_item"])) {
					$session_code_array = array_keys($_SESSION["cart_item"]);
				    if(in_array($product_array[$key]["code"],$session_code_array)) {
						$in_session = "1";
				    }
				}
			?>
			<input type="button" id="add_<?php echo $product_array[$key]["code"]; ?>" value="Add to cart" class="btnAddAction cart-action" onClick = "cartAction('add','<?php echo $product_array[$key]["code"]; ?>')" <?php if($in_session != "0") { ?>style="display:none" <?php } ?> />
			<input type="button" id="added_<?php echo $product_array[$key]["code"]; ?>" value="Added" class="btnAdded" <?php if($in_session != "1") { ?>style="display:none" <?php } ?> />
			</div>
			</form>
		</div>
	<?php
			}
	}
	?>
</div>
<div class="content" id="cart-grid">
<div id="cart-item"></div>
</div>
</BODY>
</HTML>      



              <!-- End Shopping Cart -->
             <!-- End floating Labels Form -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>NiceAdmin</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
    </div>
  </footer><!-- End Footer -->

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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
  $('#floatingProvince').change(function() {
    var id_province = $(this).val();
    //alert("province : "+id_province);
      $.ajax({
      type: "POST",
      url: "select_Amphur.php",
      data: {id:id_province,function:'provinces'},
      success: function(data){
      //    alert("success");
          $('#floatingCity').html(data); 
        },
      error:function(){
          alert("error");
      }
    });
  });

</script>