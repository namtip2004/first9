<?php
require_once 'connect_db.php';
require_once 'promotion_utils.php';

$promotionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($promotionId <= 0) {
    echo 'ไม่พบโปรโมชั่นที่ต้องการแก้ไข';
    exit;
}

ensurePromotionSupport($conn);

$stmt = $conn->prepare('SELECT * FROM promotion WHERE promotion_id = ?');
$stmt->bind_param('i', $promotionId);
$stmt->execute();
$promotion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$promotion) {
    echo 'ไม่พบโปรโมชั่นที่ต้องการแก้ไข';
    exit;
}

$status = promotionStatus($promotion['pm_start_date'], $promotion['pm_end_date']);
if ($status === 'ended') {
    header('Location: table_promotion.php?error=' . urlencode('ไม่สามารถแก้ไขโปรโมชั่นที่สิ้นสุดแล้วได้'));
    exit;
}

$startInputValue = parseDateTimeValue($promotion['pm_start_date']);
$endInputValue = parseDateTimeValue($promotion['pm_end_date']);
$startInputFormatted = $startInputValue ? $startInputValue->format('Y-m-d\TH:i') : '';
$endInputFormatted = $endInputValue ? $endInputValue->format('Y-m-d\TH:i') : '';

$services = fetchPromotionServicesWithOptions($conn, $promotionId);
$initialServicesJson = json_encode($services, JSON_UNESCAPED_UNICODE);

$columns = getPromotionColumns($conn);
$currentPercent = 0.0;
if (isset($promotion['percent'])) {
    $currentPercent = (float) $promotion['percent'];
} elseif (isset($promotion['discount'])) {
    $currentPercent = (float) $promotion['discount'];
}
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <?php include 'header.php'; ?>
  <?php include 'slidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>แก้ไขโปรโมชั่น</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body pt-4">

              <form id="promotionForm" data-promotion-form method="post" action="promotion_update.php?id=<?= $promotionId ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">ชื่อโปรโมชั่น</label>
                    <input type="text" name="pm_name" class="form-control" value="<?= htmlspecialchars($promotion['pm_name'] ?? '') ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">วัน-เวลาเริ่มต้น</label>
                    <input type="datetime-local" name="pm_start_date" class="form-control" value="<?= htmlspecialchars($startInputFormatted) ?>" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">วัน-เวลาสิ้นสุด</label>
                    <input type="datetime-local" name="pm_end_date" class="form-control" value="<?= htmlspecialchars($endInputFormatted) ?>" required>
                  </div>
                </div>

                <div class="mt-2 text-muted">
                  สถานะปัจจุบัน: <strong><?= htmlspecialchars(promotionStatusLabel($status)) ?></strong>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">บริการที่เข้าร่วมโปรโมชั่น</h5>
                  <button type="button" class="btn btn-primary" data-add-service><i class="bi bi-plus-lg"></i> เพิ่มบริการ</button>
                </div>

                <div class="alert alert-info mt-3 d-none" data-empty-state>
                  กรุณากรอกวัน-เวลาเริ่มต้นและสิ้นสุด จากนั้นคลิกปุ่ม "เพิ่มบริการ" เพื่อเลือกบริการที่ต้องการจัดโปรโมชั่น
                </div>

                <div class="row mt-3" data-selected-services></div>

                <input type="hidden" name="promotion_payload" data-payload>

                <div class="mt-4 text-center">
                  <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                  <a href="table_promotion.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal same as in add form -->
  <div class="modal fade" id="serviceSelectModal" tabindex="-1" aria-labelledby="serviceSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="serviceSelectModalLabel">เลือกบริการเข้าร่วมโปรโมชั่น</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning d-none" data-modal-warning>
            กรุณากรอกวัน-เวลาเริ่มต้นและสิ้นสุดของโปรโมชั่นก่อนเลือกบริการ
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="selectAllServices" data-select-all>
            <label class="form-check-label" for="selectAllServices">เลือกทั้งหมด</label>
          </div>
          <div class="list-group" data-service-checkboxes></div>
          <div class="text-muted mt-3 d-none" data-no-service>
            ไม่พบบริการที่สามารถจัดโปรโมชั่นได้ในช่วงเวลานี้
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" data-confirm-services>ยืนยัน</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.promotionFormConfig = {
      availableServiceUrl: 'get_available_promotion_services.php',
      optionsUrl: 'get_options.php',
      promotionId: <?= $promotionId ?>,
      initialServices: <?= $initialServicesJson ?: '[]' ?>
    };
  </script>
  <script src="assets/js/promotion-form.js"></script>

  <?php include 'footer.php'; ?>
</body>

</html>
