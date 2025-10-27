<?php
session_start();
require_once 'connect_db.php';
require_once 'promotion_utils.php';

ensurePromotionSupport($conn);

$message = isset($_GET['message']) ? trim($_GET['message']) : '';
$error = isset($_GET['error']) ? trim($_GET['error']) : '';

$columns = getPromotionColumns($conn);
$maxPercentByPromotion = [];
$percentQuery = $conn->query("SELECT promotion_id, MAX(discount_percent) AS max_percent FROM promotion_service_option GROUP BY promotion_id");
if ($percentQuery) {
    while ($row = $percentQuery->fetch_assoc()) {
        $maxPercentByPromotion[(int) $row['promotion_id']] = (float) $row['max_percent'];
    }
    $percentQuery->free();
}

$sql = "SELECT p.*, COUNT(ps.service_id) AS service_count
        FROM promotion p
        LEFT JOIN promotion_service ps ON p.promotion_id = ps.promotion_id
        GROUP BY p.promotion_id
        ORDER BY p.pm_start_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include 'header.php'; ?>
  <?php include 'slidebar.php'; ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Manage Promotions</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">

              <div class="text-end mb-3">
                <a href="form_promotion.php" class="btn btn-success"><i class="bi bi-plus"></i> Add Promotion</a>
              </div>

              <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <?= htmlspecialchars($message) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?= htmlspecialchars($error) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Promotion Name</th>
                      <th scope="col">Start Date &amp; Time</th>
                      <th scope="col">End Date &amp; Time</th>
                      <th scope="col">Status</th>
                      <th scope="col" class="text-end">Participating Services</th>
                      <th scope="col" class="text-end">Maximum Discount (%)</th>
                      <th scope="col" class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                      <?php $i = 1; ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                          $promotionId = (int) $row['promotion_id'];
                          $status = promotionStatus($row['pm_start_date'], $row['pm_end_date']);
                          $statusLabel = promotionStatusLabel($status);
                          $serviceCount = (int) $row['service_count'];
                          $maxPercent = $maxPercentByPromotion[$promotionId] ?? null;
                          if ($maxPercent === null) {
                              if (in_array('percent', $columns, true) && isset($row['percent'])) {
                                  $maxPercent = (float) $row['percent'];
                              } elseif (in_array('discount', $columns, true) && isset($row['discount'])) {
                                  $maxPercent = (float) $row['discount'];
                              } else {
                                  $maxPercent = 0.0;
                              }
                          }
                        ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td><?= htmlspecialchars($row['pm_name'] ?? '') ?></td>
                          <td><?= htmlspecialchars(formatDateTimeDisplay($row['pm_start_date'] ?? '')) ?></td>
                          <td><?= htmlspecialchars(formatDateTimeDisplay($row['pm_end_date'] ?? '')) ?></td>
                          <td>
                            <span class="badge
                              <?php if ($status === 'running'): ?> bg-success<?php elseif ($status === 'upcoming'): ?> bg-warning text-dark<?php elseif ($status === 'ended'): ?> bg-secondary<?php else: ?> bg-light text-dark<?php endif; ?>
                            ">
                              <?= htmlspecialchars($statusLabel) ?>
                            </span>
                          </td>
                          <td class="text-end"><?= $serviceCount ?></td>
                          <td class="text-end"><?= number_format((float) $maxPercent, 2) ?></td>
                          <td class="text-center">
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                              <a
                                href="promotion_detail.php?id=<?= $promotionId ?>"
                                class="btn btn-outline-primary btn-sm"
                                title="Details"
                                aria-label="Details"
                              >
                                <i class="bi bi-eye"></i>
                                <span class="visually-hidden">Details</span>
                              </a>
                              <?php if ($status !== 'ended'): ?>
                                <a
                                  href="promotion_update_form.php?id=<?= $promotionId ?>"
                                  class="btn btn-outline-secondary btn-sm"
                                  title="Edit"
                                  aria-label="Edit"
                                >
                                  <i class="bi bi-pencil-square"></i>
                                  <span class="visually-hidden">Edit</span>
                                </a>
                              <?php endif; ?>

                              <?php if ($status === 'upcoming'): ?>
                                <form action="promotion_delete.php" method="post" class="d-inline" onsubmit="return confirm('Do you want to delete this promotion?');">
                                  <input type="hidden" name="promotion_id" value="<?= $promotionId ?>">
                                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete" aria-label="Delete">
                                    <i class="bi bi-trash"></i>
                                    <span class="visually-hidden">Delete</span>
                                  </button>
                                </form>
                              <?php elseif ($status === 'running'): ?>
                                <form action="promotion_end.php" method="post" class="d-inline" onsubmit="return confirm('Do you want to end this promotion immediately?');">
                                  <input type="hidden" name="promotion_id" value="<?= $promotionId ?>">
                                  <button type="submit" class="btn btn-outline-warning btn-sm" title="End Now" aria-label="End Now">
                                    <i class="bi bi-stop-circle"></i>
                                    <span class="visually-hidden">End Now</span>
                                  </button>
                                </form>
                              <?php else: ?>

                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="8" class="text-center text-muted">No promotions found</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
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
