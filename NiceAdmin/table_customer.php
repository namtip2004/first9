<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>
  
  <main id="main" class="main pt-4">

    <div class="pagetitle">
      <h1>Customer Table</h1>
      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">        
          <div class="card">
            <div class="card-body">
             <div class="text-end mb-2">
            <a href="form_customer.php" class="btn btn-success mb-2">+ add customer</a>
          </div>
              <?php 
                require_once("connect_db.php");
                $sql = "SELECT * FROM customer";
                $result = mysqli_query($conn, $sql);
              ?>

              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NO.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Birthday</th>
                    <th>Gmail</th>
                    <th>Tel</th>
                    <th>Status</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $i = 1;
                    while($row = $result->fetch_assoc()) {
                  ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['customer_name']) ?></td>
                      <td><?= htmlspecialchars($row['gender']) ?></td>
                      <td><?= htmlspecialchars($row['birthday']) ?></td>
                      <td><?= htmlspecialchars($row['gmail']) ?></td>
                      <td><?= htmlspecialchars($row['tel']) ?></td>
                      <td><?= htmlspecialchars($row['account_status']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="customer_update_form.php?id=<?= $row['customer_id'] ?>">Edit</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" 
                           href="customer_delete.php?id=<?= $row['customer_id'] ?>" 
                           onclick="return confirm('Are you sure you want to permanently delete this customer\'s data?');">Delete</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

              <?php mysqli_close($conn); ?>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>
</body>
</html>
