<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Service From</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title"></h5> -->
              <form action="insert_service.php" method="POST" class="row g-3">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="course_name" placeholder="service" required>
                    <label for="course_name">Service Name</label>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-floating">
                    <textarea class="form-control" name="course_detail" placeholder="Description" style="height: 100px" required></textarea>
                    <label for="course_detail">Description</label>
                  </div>
                </div>

                <!-- <div class="col-md-6">
                  <div class="form-floating">
                    <input type="number" step="0.01" class="form-control" name="active_status" placeholder="active status" required>
                    <label for="active_status">active status</label>
                  </div>
                </div> -->

                <div class="col-md-6">
                  <div class="form-check form-switch mt-3">
                       <input type="hidden" name="active_status" value="0">
                        <input class="form-check-input" type="checkbox" name="active_status" value="1" id="active_status">
                        <label class="form-check-label" for="active_status">Active Status</label>
                  </div>
                </div>

                <!-- ฟอร์มเวลาเพิ่มเติม -->

<!-- อยู่ภายใน <form> เหมือนเดิม -->
<div class="col-md-12 mb-2">  
  <div class="d-flex align-items-center">
    <h5 class="mb-0 me-2">time option</h5>
    <button type="button" class="btn btn-secondary btn-sm" onclick="addTimePriceField()">+ add</button>
  </div>
</div>

<div class="col-md-12" id="new-time-price">
  <div class="row mb-2">
    <div class="col-md-3">
      <div class="input-group">
        <input type="number" name="new_times[]" class="form-control" required min="0" placeholder="">
        <span class="input-group-text">minute</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="input-group">
        <input type="number" name="new_prices[]" class="form-control" required min="0" step="0.01" placeholder="">
        <span class="input-group-text">€</span>
      </div>
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Delete</button>
    </div>
  </div>
</div>

<script>
function addTimePriceField() {
  const container = document.getElementById('new-time-price');
  const html = `
    <div class="row mb-2">
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="new_times[]" class="form-control" required min="0" placeholder="">
          <span class="input-group-text">minute</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="new_prices[]" class="form-control" required min="0" step="0.01" placeholder="">
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

                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                  <button type="reset" class="btn btn-secondary">Reset</button>
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
