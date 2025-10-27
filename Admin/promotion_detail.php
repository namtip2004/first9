<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

$promotionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($promotionId <= 0) {
    echo 'Promotion not found.';
    exit;
}

$stmt = $conn->prepare('SELECT * FROM promotion WHERE promotion_id = ?');
$stmt->bind_param('i', $promotionId);
$stmt->execute();
$promotion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$promotion) {
    echo 'Promotion not found.';
    exit;
}

$services = fetchPromotionServicesWithOptions($conn, $promotionId);
$columns = getPromotionColumns($conn);
$status = promotionStatus($promotion['pm_start_date'], $promotion['pm_end_date']);
$maxPercent = 0.0;
foreach ($services as $service) {
    foreach ($service['options'] as $option) {
        if ($option['discount_percent'] > $maxPercent) {
            $maxPercent = $option['discount_percent'];
        }
    }
}
if ($maxPercent === 0.0) {
    if (isset($promotion['percent'])) {
        $maxPercent = (float) $promotion['percent'];
    } elseif (isset($promotion['discount'])) {
        $maxPercent = (float) $promotion['discount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include 'header.php'; ?>
  <?php include 'slidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Promotion Details</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body pt-4">

              <div class="row mb-3">
                <div class="col-md-6">
                  <h5>General Information</h5>
                  <dl class="row mb-0">
                    <dt class="col-sm-4">Promotion Name</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($promotion['pm_name'] ?? '-') ?></dd>
                    <dt class="col-sm-4">Start Date &amp; Time</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(formatDateTimeDisplay($promotion['pm_start_date'] ?? '')) ?></dd>
                    <dt class="col-sm-4">End Date &amp; Time</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(formatDateTimeDisplay($promotion['pm_end_date'] ?? '')) ?></dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(promotionStatusLabel($status)) ?></dd>
                    <dt class="col-sm-4">Maximum Discount</dt>
                    <dd class="col-sm-8"><?= number_format($maxPercent, 2) ?>%</dd>
                    <?php if (in_array('pm_created_at', $columns, true) && !empty($promotion['pm_created_at'])): ?>
                      <dt class="col-sm-4">Created At</dt>
                      <dd class="col-sm-8"><?= htmlspecialchars(formatDateTimeDisplay($promotion['pm_created_at'])) ?></dd>
                    <?php endif; ?>
                  </dl>
                </div>
                <div class="col-md-6">
                  <h5>Notes</h5>
                  <?php if (!empty($promotion['description'])): ?>
                    <p><?= nl2br(htmlspecialchars($promotion['description'])) ?></p>
                  <?php else: ?>
                    <p class="text-muted">No additional details.</p>
                  <?php endif; ?>
                </div>
              </div>

              <h5>Services &amp; Discounts</h5>
              <?php if (empty($services)): ?>
                <p class="text-muted">No services in this promotion.</p>
              <?php else: ?>
                <?php foreach ($services as $service): ?>
                  <div class="card border mb-3">
                    <div class="card-header bg-light fw-semibold">
                      <?= htmlspecialchars($service['service_name']) ?>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center" style="width: 120px;">Duration (minutes)</th>
                              <th class="text-end" style="width: 140px;">Regular Price</th>
                              <th class="text-center" style="width: 120px;">Discount (%)</th>
                              <th class="text-end" style="width: 140px;">Discount Amount</th>
                              <th class="text-end" style="width: 140px;">Final Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($service['options'] as $option): ?>
                              <tr>
                                <td class="text-center"><?= (int) $option['duration'] ?></td>
                                <td class="text-end">€<?= number_format((float) $option['price'], 2) ?></td>
                                <td class="text-center"><?= number_format((float) $option['discount_percent'], 2) ?></td>
                                <td class="text-end">€<?= number_format((float) $option['discount_amount'], 2) ?></td>
                                <td class="text-end">€<?= number_format((float) $option['final_price'], 2) ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>

              <div class="mt-3 text-center">
                <a href="promotion_update_form.php?id=<?= $promotionId ?>" class="btn btn-primary">Edit</a>
                <a href="table_promotion.php" class="btn btn-secondary">Back</a>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include 'footer.php'; ?>
</body>

</html>
