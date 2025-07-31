<!DOCTYPE html>
<html lang="en">

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

$sql_tags = "SELECT tag.tag_name 
             FROM tag_service
             INNER JOIN tag ON tag_service.tag_id = tag.tag_id 
             WHERE tag_service.service_id = '$service_id'";
$res_tags = mysqli_query($conn, $sql_tags);
?>


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

<?php if (!empty($service['coverimg'])): ?>
  <div class="my-3">
    <h5>Service Image</h5>
    <div style="width: 150px; height: 150px; overflow: hidden; border: 1px solid #ccc; border-radius: 8px; cursor: pointer;"
         data-bs-toggle="modal" data-bs-target="#imageModal">
      <img src="assets/img/<?= htmlspecialchars($service['coverimg']) ?>" 
           alt="Service Image" 
           style="width: 100%; height: 100%; object-fit: contain;">
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="min-height: 300px;">
  <img src="assets/img/<?= htmlspecialchars($service['coverimg']) ?>" alt="Large Image" style="max-width: 300px; height: auto;">
</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <p class="text-muted">No image uploaded.</p>
<?php endif; ?>
  

<h5 class="mt-3">Tags</h5>
<?php if (mysqli_num_rows($res_tags) > 0): ?>
  <div class="tag-box1">
    <?php while ($tag = mysqli_fetch_assoc($res_tags)): ?>
      <span class="tag-pill">
        <?= htmlspecialchars($tag['tag_name']) ?>
      </span>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <p class="text-muted">No tags assigned.</p>
<?php endif; ?>

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
              <a href="service_update_form.php?id=<?= $service['service_id'] ?>" class="btn btn-primary mt-3">Edit</a>
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
