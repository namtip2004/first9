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
      <h1>Promotion Table</h1>
      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">

              <div class="text-end mb-2">
                <a href="form_promotion.php" class="btn btn-success mb-2">+ Add Promotion</a>
              </div>

              <?php 
              require_once("connect_db.php");
              $sql = "SELECT * FROM promotion ORDER BY pm_created_at DESC";
              $result = mysqli_query($conn, $sql);
              ?>

              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Promotion Name</th>
                    <th>Discount (%)</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Apply to All</th>
                    <th>Status</th>
                    <th>Detail</th>
                    <th>Edit</th>
                    <!-- <th>Delete</th> -->
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $i = 1;
                  while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['pm_name']) ?></td>
                      <td><?= htmlspecialchars($row['discount']) ?></td>
                      <td><?= htmlspecialchars($row['pm_start_date']) ?></td>
                      <td><?= htmlspecialchars($row['pm_end_date']) ?></td>
                      <td><?= $row['apply_to_all'] ? 'Yes' : 'No' ?></td>
                      <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="promotion_detail.php?id=<?= $row['promotion_id'] ?>">Detail</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="promotion_update_form.php?id=<?= $row['promotion_id'] ?>">Edit</a>
                      </td>
                      <!-- <td>
                        <a class="btn btn-outline-danger btn-sm" href="promotion_delete.php?id=<?= $row['promotion_id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this promotion?');">Delete</a>
                      </td> -->
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

  <?php include("footer.php"); ?>
</body>

</html>
