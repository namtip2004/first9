<?php
require_once("connect_db.php");

// รับค่า staff_ID จากพารามิเตอร์ URL
$staff_id = $_GET['id'];

// ดึงข้อมูลพนักงานจากฐานข้อมูล
$sql = "SELECT * FROM staff WHERE staff_ID = $staff_id";
$result = mysqli_query($conn, $sql);
$staff = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Edit Staff</h1>
      <nav><ol class="breadcrumb"></ol></nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Edit Staff Info</h5>

              <form class="row g-3" action="update_staff.php?id=<?= $staff['staff_ID'] ?>" method="POST">
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingfName" name="floatingfName" value="<?= htmlspecialchars($staff['staff_F_name']) ?>">
                    <label for="floatingfName">First Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatinglName" name="floatinglName" value="<?= htmlspecialchars($staff['staff_L_name']) ?>">
                    <label for="floatinglName">Last Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatinggender" name="floatinggender" value="<?= htmlspecialchars($staff['staff_gender']) ?>">
                    <label for="floatinggender">Gender</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="number" class="form-control" id="floatingAge" name="floatingAge" value="<?= htmlspecialchars($staff['staff_age']) ?>">
                    <label for="floatingAge">Age</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingnation" name="floatingnation" value="<?= htmlspecialchars($staff['staff_nationality']) ?>">
                    <label for="floatingnation">Nationality</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="floatingEmail" name="floatingEmail" value="<?= htmlspecialchars($staff['staff_mail']) ?>">
                    <label for="floatingEmail">Email</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingPhone" name="floatingPhone" value="<?= htmlspecialchars($staff['staff_tel']) ?>">
                    <label for="floatingPhone">Phone</label>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-floating">
                    <textarea class="form-control" id="floatingAddress" name="floatingAddress" style="height: 100px;"><?= htmlspecialchars($staff['staff_address']) ?></textarea>
                    <label for="floatingAddress">Address</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingstdate" name="floatingstdate" value="<?= htmlspecialchars($staff['start_job']) ?>">
                    <label for="floatingstdate">Start Job</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingendate" name="floatingendate" value="<?= htmlspecialchars($staff['end_job']) ?>">
                    <label for="floatingendate">End Job</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" id="floatingStatus" name="floatingStatus">
                      <option value="active" <?= $staff['staff_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $staff['staff_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <label for="floatingStatus">Status</label>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Update</button>
                  <a href="staff_table.php" class="btn btn-secondary">Cancel</a>
                </div>
              </form>

            </div>
          </div>

        </div>
      </div>
    </section>
  </main>

  <?php include("footer.php"); ?>
</body>

</html>
