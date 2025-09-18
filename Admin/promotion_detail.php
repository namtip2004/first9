<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "No promotion ID provided.";
  exit;
}

$promotion_id = intval($_GET['id']);

// Get promotion details
$stmt = $conn->prepare("SELECT * FROM promotion WHERE promotion_id = ?");
$stmt->bind_param("i", $promotion_id);
$stmt->execute();
$result = $stmt->get_result();
$promotion = $result->fetch_assoc();

if (!$promotion) {
  echo "Promotion not found.";
  exit;
}

// Get related services
$services = [];
if (!$promotion['apply_to_all']) {
  $stmt2 = $conn->prepare("
    SELECT s.service_name 
    FROM promotion_service ps 
    JOIN service s ON ps.service_id = s.service_id 
    WHERE ps.promotion_id = ?
  ");
  $stmt2->bind_param("i", $promotion_id);
  $stmt2->execute();
  $services_result = $stmt2->get_result();

  while ($row = $services_result->fetch_assoc()) {
    $services[] = $row['service_name'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Promotion Detail</title>
  <meta charset="UTF-8">
  <link href="assets/css/style.css" rel="stylesheet">
  <!-- Include your CSS and bootstrap here -->
</head>

<body>

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">

  <div class="pagetitle">
    <h1>Promotion Detail</h1>
    <nav><ol class="breadcrumb"></ol></nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body pt-4">

            <!-- <h5 class="card-title">Promotion Information</h5> -->

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Name</label>
              <div class="col-sm-9 pt-2"><?= htmlspecialchars($promotion['pm_name']) ?></div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Description</label>
              <div class="col-sm-9 pt-2"><?= nl2br(htmlspecialchars($promotion['description'])) ?></div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Discount (%)</label>
              <div class="col-sm-9 pt-2"><?= htmlspecialchars($promotion['discount']) ?>%</div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Apply to All Services</label>
              <div class="col-sm-9 pt-2"><?= $promotion['apply_to_all'] ? 'Yes' : 'No' ?></div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Start Date</label>
              <div class="col-sm-9 pt-2"><?= htmlspecialchars($promotion['pm_start_date']) ?></div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">End Date</label>
              <div class="col-sm-9 pt-2"><?= htmlspecialchars($promotion['pm_end_date']) ?></div>
            </div>

            <div class="row mb-2">
              <label class="col-sm-3 col-form-label">Status</label>
              <div class="col-sm-9 pt-2"><?= $promotion['active'] ? 'Active' : 'Inactive' ?></div>
            </div>

            <?php if (!$promotion['apply_to_all']): ?>
              <div class="row mt-4">
                <label class="col-sm-3 col-form-label">Services</label>
                <div class="col-sm-9">
                  <ul class="list-group">
                    <?php foreach ($services as $service): ?>
                      <li class="list-group-item"><?= htmlspecialchars($service) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

            <div class="text-center mt-4">
              <a href="promotion_update_form.php?id=<?= $promotion['promotion_id'] ?>" class="btn btn-primary mt-3">Edit</a>
              <a href="table_promotion.php" class="btn btn-secondary mt-3">Back</a>
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
