<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

ensurePromotionSupport($conn);

$defaultStart = (new DateTimeImmutable('now'))->format('Y-m-d\TH:i');
$defaultEnd = (new DateTimeImmutable('now +1 day'))->format('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include 'header.php'; ?>
  <?php include 'slidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Add Promotion</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body pt-4">

              <form id="promotionForm" data-promotion-form method="post" action="insert_promotion.php">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Promotion Name</label>
                    <input type="text" name="pm_name" class="form-control" placeholder="Enter promotion name" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Start Date &amp; Time</label>
                    <input type="datetime-local" name="pm_start_date" class="form-control" value="<?= htmlspecialchars($defaultStart) ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">End Date &amp; Time</label>
                    <input type="datetime-local" name="pm_end_date" class="form-control" value="<?= htmlspecialchars($defaultEnd) ?>" required>
                  </div>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Services in Promotion</h5>
                  <button type="button" class="btn btn-primary" data-add-service><i class="bi bi-plus-lg"></i> Add Service</button>
                </div>

                <div class="alert alert-info mt-3" data-empty-state>
                  Please enter the start and end date/time, then click the "Add Service" button to choose services for the promotion.
                </div>

                <div class="row mt-3" data-selected-services></div>

                <input type="hidden" name="promotion_payload" data-payload>

                <div class="mt-4 text-center">
                  <button type="submit" class="btn btn-success">Save Promotion</button>
                  <a href="table_promotion.php" class="btn btn-secondary">Cancel</a>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal: Select services -->
  <div class="modal fade" id="serviceSelectModal" tabindex="-1" aria-labelledby="serviceSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="serviceSelectModalLabel">Select Services for Promotion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning d-none" data-modal-warning>
            Please provide the promotion start and end date before selecting services.
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="selectAllServices" data-select-all>
            <label class="form-check-label" for="selectAllServices">Select All</label>
          </div>
          <div class="list-group" data-service-checkboxes></div>
          <div class="text-muted mt-3 d-none" data-no-service>
            No services are available for this time range.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" data-confirm-services>Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.promotionFormConfig = {
      availableServiceUrl: 'get_available_promotion_services.php',
      optionsUrl: 'get_options.php',
      promotionId: null,
      initialServices: []
    };
  </script>
  <script src="assets/js/promotion-form.js"></script>

  <?php include 'footer.php'; ?>
</body>

</html>
