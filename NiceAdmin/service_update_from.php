<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID";
  exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM service WHERE service_id = '$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "ไม่พบข้อมูลบริการ";
  exit;
}

// ดึงข้อมูลเวลา-ราคา
$sql_time = "SELECT * FROM service_option WHERE service_id = '$id'";
$res_time = mysqli_query($conn, $sql_time);
?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Edit Service</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <form action="update_service.php" method="POST" class="row g-3">
                <input type="hidden" name="service_id" value="<?= $data['service_id'] ?>">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="service_name" value="<?= $data['service_name'] ?>" required>
                    <label for="service_name">Service Name</label>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-floating">
                    <textarea class="form-control" name="service_detail" style="height: 100px" required><?= $data['description'] ?></textarea>
                    <label for="service_detail">Description</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch mt-3">
                    <input type="hidden" name="active_status" value="0">
                    <input class="form-check-input" type="checkbox" name="active_status" value="1" id="active_status"
                      <?= $data['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active_status">Active Status</label>
                  </div>
                </div>

                <!-- เวลาที่มีอยู่ -->
                <div class="col-md-12 mb-2">
                  <h5>Existing time options</h5>
                  <?php while ($time = mysqli_fetch_assoc($res_time)) { ?>
                    <div class="row mb-2 align-items-center">
                      <input type="hidden" name="existing_time_ids[]" value="<?= $time['option_id'] ?>">
                      <div class="col-md-3">
                        <div class="input-group">
                          <input type="number" name="existing_times[<?= $time['option_id'] ?>]" class="form-control"
                            value="<?= $time['duration'] ?>" min="0" required>
                          <span class="input-group-text">minute</span>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="input-group">
                          <input type="number" name="existing_prices[<?= $time['option_id'] ?>]" class="form-control"
                            value="<?= $time['price'] ?>" min="0" step="0.01" required>
                          <span class="input-group-text">€</span>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <a href="delete_service_time.php?id=<?= $time['option_id'] ?>&service=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to permanently delete this time option\'s data?');">Delete</a>
                      </div>
                    </div>
                  <?php } ?>
                </div>

                <!-- เพิ่มเวลาใหม่ -->
                <div class="col-md-12 mb-2">
                  <div class="d-flex align-items-center">
                    <h5 class="mb-0 me-2">Add time option</h5>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addTimePriceField()">+ add</button>
                  </div>
                </div>

                <div class="col-md-12" id="new-time-price"></div>

                <script>
                  function addTimePriceField() {
                    const container = document.getElementById('new-time-price');
                    const html = `
                      <div class="row mb-2">
                        <div class="col-md-3">
                          <div class="input-group">
                            <input type="number" name="new_times[]" class="form-control" min="0" required>
                            <span class="input-group-text">minute</span>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="input-group">
                            <input type="number" name="new_prices[]" class="form-control" min="0" step="0.01" required>
                            <span class="input-group-text">€</span>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Delete</button>
                        </div>
                      </div>`;
                    container.insertAdjacentHTML('beforeend', html);
                  }
                </script>

                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <a href="table_service.php" class="btn btn-secondary">Cancel</a>
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
