<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "No promotion ID provided.";
  exit;
}

$promotion_id = (int)$_GET['id'];

// Get promotion
$stmt = $conn->prepare("SELECT * FROM promotion WHERE promotion_id = ?");
$stmt->bind_param("i", $promotion_id);
$stmt->execute();
$promotion = $stmt->get_result()->fetch_assoc();

if (!$promotion) {
  echo "Promotion not found.";
  exit;
}

// Get all services
$services = mysqli_query($conn, "SELECT * FROM service ORDER BY service_name ASC");

// Get selected services (if not apply_to_all)
$selected_services = [];
if (!$promotion['apply_to_all']) {
  $result = mysqli_query($conn, "SELECT service_id FROM promotion_service WHERE promotion_id = $promotion_id");
  while ($row = mysqli_fetch_assoc($result)) {
    $selected_services[] = $row['service_id'];
  }
}

$pm_start_date = date('Y-m-d', strtotime($promotion['pm_start_date']));
$pm_end_date = date('Y-m-d', strtotime($promotion['pm_end_date']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Promotion</title>
  <meta charset="UTF-8">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Edit Promotion</h1>
    <nav><ol class="breadcrumb"></ol></nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-10">

        <div class="card">
          <div class="card-body pt-4">
            <form action="promotion_update.php?id=<?= $promotion_id ?>" method="post">

              <div class="mb-3">
                <label class="form-label">Promotion Name</label>
                <input type="text" class="form-control" name="pm_name" value="<?= htmlspecialchars($promotion['pm_name']) ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($promotion['description']) ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Discount (%)</label>
                <input type="number" class="form-control" name="discount" value="<?= $promotion['discount'] ?>" min="0" max="100" required>
              </div>

              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="apply_to_all" id="applyToAll" <?= $promotion['apply_to_all'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="applyToAll">Apply to All Services</label>
              </div>

              <div class="mb-3" id="servicesList" <?= $promotion['apply_to_all'] ? 'style="display:none;"' : '' ?>>
                <label class="form-label">Select Services</label>
                <div class="row">
                  <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="service_ids[]" value="<?= $service['service_id'] ?>"
                          <?= in_array($service['service_id'], $selected_services) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= htmlspecialchars($service['service_name']) ?></label>
                      </div>
                    </div>
                  <?php } ?>
                </div>
              </div>


              <div class="row mb-3">
                <div class="col">
                  <label class="form-label">Start Date</label>
                  <input type="date" class="form-control" name="pm_start_date" value="<?= $pm_start_date ?>" required>
                </div>
                <div class="col">
                  <label class="form-label">End Date</label>
                 <input type="date" class="form-control" name="pm_end_date" value="<?= $pm_end_date ?>" required>
                </div>
              </div>

              <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="active" id="activeSwitch" <?= $promotion['active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="activeSwitch">Active</label>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">Update Promotion</button>
                <a href="table_promotion.php" class="btn btn-secondary">Cancel</a>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<script>
document.getElementById("applyToAll").addEventListener("change", function() {
  document.getElementById("servicesList").style.display = this.checked ? "none" : "block";
});
</script>

<?php include("footer.php"); ?>
</body>
</html>
