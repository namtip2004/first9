<?php
require_once("connect_db.php");

// Fetch all services
$services = [];
$sql = "SELECT * FROM service";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $services[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Promotion</title>
</head>
<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Add Promotion</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body pt-4">
              <form action="insert_promotion.php" method="POST">
                <div class="row g-3">

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" name="pm_name" class="form-control" placeholder="Promotion Name" required>
                      <label>Promotion Name</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="number" name="discount" class="form-control" placeholder="Discount %" required>
                      <label>Discount (%)</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="date" name="pm_start_date" class="form-control" required>
                      <label>Start Date</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="date" name="pm_end_date" class="form-control" required>
                      <label>End Date</label>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <label>Apply to all services?</label><br>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="apply_to_all" id="applyYes" value="1" checked onclick="toggleServiceList(true)">
                      <label class="form-check-label" for="applyYes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="apply_to_all" id="applyNo" value="0" onclick="toggleServiceList(false)">
                      <label class="form-check-label" for="applyNo">No</label>
                    </div>
                  </div>

                  <div class="col-md-12 mt-2" id="serviceList" style="display: none;">
                    <label>Select services for this promotion:</label>
                    <?php foreach ($services as $service): ?>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="service_ids[]" value="<?= $service['service_id'] ?>" id="service<?= $service['service_id'] ?>">
                        <label class="form-check-label" for="service<?= $service['service_id'] ?>">
                          <?= htmlspecialchars($service['service_name']) ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <div class="col-md-12 mt-3">
                    <div class="form-floating">
                      <textarea name="description" class="form-control" style="height: 100px"></textarea>
                      <label>Description</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label for="active" class="form-label">Status</label>
                    <select name="active" id="active" class="form-select">
                      <option value="1" selected>Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>

                </div>

                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary">Add Promotion</button>
                  <a href="promotion_table.php" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    function toggleServiceList(isAll) {
      document.getElementById('serviceList').style.display = isAll ? 'none' : 'block';
    }
  </script>

  <?php include("footer.php"); ?>
</body>
</html>
