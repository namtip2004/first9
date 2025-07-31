<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Staff Table</h1>
     
      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title">Staff</h5> -->
      <div class="text-end mb-2">
   <a href="form_staff.php" class="btn btn-success mb-2">+ add staff</a>
</div>
              <?php 
              require_once("connect_db.php");
              $sql = "SELECT * FROM staff";
              $result = mysqli_query($conn, $sql);
              ?>

              <!-- Table -->
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NO.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Birthday</th>
                    <th>Gmail</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Start Job</th>
                    <th>End Job</th>
                    <th>Status</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                   
                  <?php 
                  $i = 1;
                  while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['staff_name']) ?></td>
                      <td><?= htmlspecialchars($row['st_gender']) ?></td>
                      <td><?= htmlspecialchars($row['st_age']) ?></td>
                      <td><?= htmlspecialchars($row['st_birthday']) ?></td>
                      <td><?= htmlspecialchars($row['st_gmail']) ?></td>
                      <td><?= htmlspecialchars($row['st_tel']) ?></td>
                      <td><?= htmlspecialchars($row['st_address']) ?></td>
                      <td><?= htmlspecialchars($row['start_job']) ?></td>
                      <td><?= htmlspecialchars($row['end_job']) ?></td>
                      <td><?= htmlspecialchars($row['st_status']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="staff_update_form.php?id=<?= $row['staff_id'] ?>">Edit</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" href="staff_delete.php?id=<?= $row['staff_id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this staff\'s data?');">Delete</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <!-- End Table -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>
</body>

</html>
