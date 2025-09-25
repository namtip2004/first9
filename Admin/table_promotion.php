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
      <h1>จัดการโปรโมชั่น</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">

              <div class="text-end mb-3">
                <a href="form_promotion.php" class="btn btn-success"><i class="bi bi-plus"></i> เพิ่มโปรโมชั่น</a>
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
                      <th scope="col">ชื่อโปรโมชั่น</th>
                      <th scope="col">วัน-เวลาเริ่มต้น</th>
                      <th scope="col">วัน-เวลาสิ้นสุด</th>
                      <th scope="col">สถานะ</th>
                      <th scope="col" class="text-end">บริการที่เข้าร่วม</th>
                      <th scope="col" class="text-end">ส่วนลดสูงสุด (%)</th>
                      <th scope="col" class="text-center">การจัดการ</th>
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
                                title="รายละเอียด"
                                aria-label="รายละเอียด"
                              >
                                <i class="bi bi-eye"></i>
                                <span class="visually-hidden">รายละเอียด</span>
                              </a>
                              <?php if ($status !== 'ended'): ?>
                                <a
                                  href="promotion_update_form.php?id=<?= $promotionId ?>"
                                  class="btn btn-outline-secondary btn-sm"
                                  title="แก้ไข"
                                  aria-label="แก้ไข"
                                >
                                  <i class="bi bi-pencil-square"></i>
                                  <span class="visually-hidden">แก้ไข</span>
                                </a>
                              <?php endif; ?>

                              <?php if ($status === 'upcoming'): ?>
                                <form action="promotion_delete.php" method="post" class="d-inline" onsubmit="return confirm('ต้องการลบโปรโมชั่นนี้หรือไม่?');">
                                  <input type="hidden" name="promotion_id" value="<?= $promotionId ?>">
                                  <button type="submit" class="btn btn-outline-danger btn-sm" title="ลบ" aria-label="ลบ">
                                    <i class="bi bi-trash"></i>
                                    <span class="visually-hidden">ลบ</span>
                                  </button>
                                </form>
                              <?php elseif ($status === 'running'): ?>
                                <form action="promotion_end.php" method="post" class="d-inline" onsubmit="return confirm('ต้องการสิ้นสุดโปรโมชั่นนี้ทันทีหรือไม่?');">
                                  <input type="hidden" name="promotion_id" value="<?= $promotionId ?>">
                                  <button type="submit" class="btn btn-outline-warning btn-sm" title="สิ้นสุดทันที" aria-label="สิ้นสุดทันที">
                                    <i class="bi bi-stop-circle"></i>
                                    <span class="visually-hidden">สิ้นสุดทันที</span>
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
                        <td colspan="8" class="text-center text-muted">ยังไม่มีข้อมูลโปรโมชั่น</td>
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
