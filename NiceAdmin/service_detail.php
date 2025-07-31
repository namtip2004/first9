<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID";
  exit;
}

$service_id = $_GET['id'];

// ดึงข้อมูล service
$sql = "SELECT * FROM service WHERE service_ID = '$service_id'";
$result = mysqli_query($conn, $sql);
$service = mysqli_fetch_assoc($result);

if (!$service) {
  echo "ไม่พบข้อมูลบริการ";
  exit;
}

// ดึง service options (เวลา + ราคา)
$sql_options = "SELECT * FROM service_option WHERE service_id = '$service_id'";
$res_options = mysqli_query($conn, $sql_options);
?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Service Detail</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-10">
          <div class="card">
            <div class="card-body pt-4">

              <h4><?= htmlspecialchars($service['service_name']) ?></h4>
              <p><strong>Status:</strong>
                <?= $service['is_active'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>' ?>
              </p>

              <p><strong>Description:</strong><br>
                <?= nl2br(htmlspecialchars($service['description'])) ?>
              </p>

              <p><strong>Create at:</strong><br>
                <?= nl2br(htmlspecialchars($service['s_created_at'])) ?>
              </p>              

              <p><strong>Update at:</strong><br>
                <?= nl2br(htmlspecialchars($service['s_updated_at'])) ?>
              </p>

              <hr>

              <h5>Time Options</h5>
              <?php if (mysqli_num_rows($res_options) > 0): ?>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Duration (Minutes)</th>
                      <th>Price (€)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_options)): ?>
                      <tr>
                        <td><?= $row['duration'] ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="text-muted">No time options available.</p>
              <?php endif; ?>
                <div class="text-center mt-4">
              <a href="service_update_from.php?id=<?= $service['service_id'] ?>" class="btn btn-primary mt-3">Edit</a>
              <a href="table_service.php" class="btn btn-secondary mt-3">Back</a>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include("footer.php"); ?>
</body>
</html>
