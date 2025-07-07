<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">



<body>

  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Member form</h1>
      <nav>
        <ol class="breadcrumb">
         
        </ol>
      </nav>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        <div class="col-lg-12 ">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title"></h5>

              <!-- Floating Labels Form -->
              <form class="row g-3" action="insert_staff.php" method="POST">
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingfName" name="floatingfName" placeholder="first name">
                    <label for="floatingfName">first name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatinglName" name="floatinglName" placeholder="last Name">
                    <label for="floatinglName">last Name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatinggender" name="floatinggender" placeholder="gender">
                    <label for="floatingPhone">gender</label>
                  </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="floatingAge" name="floatingAge" placeholder="Age" min="0">
                        <label for="floatingAge">Age</label>
                     </div>
                </div>
  
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingnation" name="floatingnation" placeholder="nation">
                    <label for="floatingnation">nationality</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="floatingEmail" name="floatingEmail" placeholder="Your Email">
                    <label for="floatingEmail">Your Email</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingPhone" name="floatingPhone" placeholder="phone number">
                    <label for="floatingPhone">phone number</label>
                  </div>
                </div>
                 <div class="col-12">
                  <div class="form-floating">
                    <textarea class="form-control" placeholder="Address" id="floatingAddress" name="floatingAddress" style="height: 100px;"></textarea>
                    <label for="floatingAddress">Address</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingstdate" name="floatingstdate" placeholder="stdate">
                    <label for="inputDate">start_job</label>
                  </div>
                </div>                
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                  <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
              </form><!-- End floating Labels Form -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

 <?php include("footer.php"); ?>
</body>

</html>
