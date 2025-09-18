<?php
require_once("connect_db.php");
if (!isset($_GET['id'])) { echo "Booking ID not found."; exit; }
$booking_id = (int)$_GET['id'];

// Booking + Customer + Staff
$stmt = $conn->prepare("
  SELECT b.booking_id, b.customer_id, b.staff_id, b.booking_date, b.time_start, b.time_end,
         c.customer_name, c.gmail, c.tel,
         s.staff_name
  FROM booking b
  LEFT JOIN customer c ON c.customer_id = b.customer_id
  LEFT JOIN staff    s ON s.staff_id    = b.staff_id
  WHERE b.booking_id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) { echo "ไม่พบข้อมูลการจอง"; exit; }
$stmt->close();

// รายการบริการ
$svc = $conn->prepare("
  SELECT s.service_name, o.duration, o.price
  FROM booking_seviceop bs
  JOIN service_option o ON o.option_id = bs.option_id
  JOIN service s ON s.service_id = o.service_id
  WHERE bs.booking_id = ?
");
$svc->bind_param("i", $booking_id);
$svc->execute();
$selected = $svc->get_result()->fetch_all(MYSQLI_ASSOC);
$svc->close();

$totalMinutes = (int)array_sum(array_column($selected,'duration'));
$totalPrice   = array_sum(array_map(fn($r)=> (float)$r['price'], $selected));

$staffs = $conn->query("SELECT staff_id, staff_name FROM staff WHERE st_status='active' ORDER BY staff_name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>แก้ไขการจอง</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body { background:#fff; }
    .section-title{font-weight:600; margin-bottom:.5rem}
    .form-label{font-weight:500}
    .summary-table th{background:#f8f9fa; font-weight:600}
  </style>
</head>

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

  <main id="main" class="main pt-5 mt-5">
        <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
  <h1 class="mb-3">แก้ไขการจอง #<?= htmlspecialchars($booking['booking_id']) ?></h1>

  <form action="booking_update_process.php" method="POST" id="editForm">
    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">

    <!-- ส่วนแก้ไข -->

    <h5 class="section-title">ข้อมูลที่สามารถแก้ไขได้</h5>
    <div class="row mb-3">
    <div class="col-md-6">
  <label class="form-label">
    วันที่จอง
    <small class="text-muted ms-2">
      วันที่เดิม: <?= htmlspecialchars(substr($booking['booking_date'], 0, 10)) ?>
    </small>
  </label>
  <input type="text"
         name="booking_date"
         id="booking_date"
         class="form-control"
         value=""  
         required>
  <div class="form-text">เลือกได้ไม่เกิน 3 เดือนนับจากวันนี้</div>
</div>
      <div class="col-md-3">
        <label class="form-label">เวลาเริ่ม</label>
        <select name="start_time" id="start_time" class="form-select" required>
          <option value="">เลือกเวลา</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">ผู้ให้บริการ</label>
        <select name="staff_id" id="staff_id" class="form-select" required>
          <option value="">เลือกผู้ให้บริการ</option>
          <?php while($s = $staffs->fetch_assoc()): ?>
            <option value="<?= $s['staff_id'] ?>"><?= htmlspecialchars($s['staff_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <!-- ส่วนสรุป -->
    <h5 class="section-title">สรุปข้อมูลการจอง</h5>
    <table class="table table-bordered summary-table mb-4">
      <tr>
        <th style="width:30%">หมายเลขการจอง</th>
        <td>#<?= htmlspecialchars($booking['booking_id']) ?></td>
      </tr>
      <tr>
        <th>ลูกค้า</th>
        <td>
          <?= htmlspecialchars($booking['customer_name'] ?? '—') ?><br>
          <?php if(!empty($booking['tel'])): ?>โทร. <?= htmlspecialchars($booking['tel']) ?><br><?php endif; ?>
          <?php if(!empty($booking['email'])): ?><?= htmlspecialchars($booking['email']) ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th>วัน–เวลา (เดิม)</th>
        <td><?= htmlspecialchars($booking['booking_date']) ?>, <?= htmlspecialchars($booking['time_start']) ?>–<?= htmlspecialchars($booking['time_end']) ?></td>
      </tr>
      <tr>
        <th>ผู้ให้บริการ (เดิม)</th>
        <td><?= htmlspecialchars($booking['staff_name'] ?? '—') ?></td>
      </tr>
      <tr>
        <th>ระยะเวลารวม</th>
        <td><?= (int)$totalMinutes ?> นาที</td>
      </tr>
      <tr>
        <th>ราคารวมโดยประมาณ</th>
        <td>฿<?= number_format($totalPrice,2) ?></td>
      </tr>
    </table>

    <!-- รายการบริการ -->
    <h5 class="section-title">รายละเอียดบริการ</h5>
    <?php if (empty($selected)): ?>
      <p class="text-muted">ไม่มีรายการบริการ</p>
    <?php else: ?>
      <table class="table table-bordered table-sm">
        <thead class="table-light">
          <tr>
            <th>บริการ</th>
            <th class="text-center" style="width:120px;">นาที</th>
            <th class="text-end" style="width:140px;">ราคา (฿)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($selected as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['service_name']) ?></td>
              <td class="text-center"><?= (int)$row['duration'] ?></td>
              <td class="text-end"><?= number_format($row['price'],2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="fw-semibold">
            <td class="text-end">รวม</td>
            <td class="text-center"><?= (int)$totalMinutes ?></td>
            <td class="text-end">฿<?= number_format($totalPrice,2) ?></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>

    <div class="mt-4">
      <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
      <a href="booking_detail.php?id=<?= $booking_id ?>" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
  </form>
  
            </div> <!-- .card-body -->
          </div> <!-- .card -->
        </div> <!-- .col -->
      </div> <!-- .row -->
    </section>
</main>

<?php include("footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
const TOTAL_MINUTES = <?= (int)$totalMinutes ?>;

function loadTimes(dateStr) {
  if (!dateStr || !TOTAL_MINUTES) return;

  fetch(`get_available_times.php?date=${dateStr}&duration=${TOTAL_MINUTES}`)
    .then(r => r.json())
    .then(list => {
      const sel = document.getElementById('start_time');
      // ตัดวินาทีออกให้เหลือ HH:MM เพื่อให้เทียบกับลิสต์ได้
      const current = "<?= htmlspecialchars(substr($booking['time_start'], 0, 5)) ?>";

      // 1) แก้ placeholder: ห้ามใช้ value="current"
      sel.innerHTML = '<option value="">เลือกเวลา</option>';

      // เติมเวลาที่ว่าง + เช็คว่าเจอเวลาเดิมไหม
      let found = false;
      list.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        if (t === current) {
          opt.selected = true;
          found = true;
        }
        sel.appendChild(opt);
      });

      // ถ้าเวลาเดิมไม่อยู่ในลิสต์ → ใส่รายการแจ้งเตือน (disabled) ให้ผู้ใช้เห็นว่า “เวลาเดิมไม่ว่าง”
      if (current && !found) {
        const cur = document.createElement('option');
        cur.value = '';
        cur.textContent = `เวลาปัจจุบัน: ${current} (ไม่ว่าง)`;
        cur.selected = true;
        cur.disabled = true;
        sel.insertBefore(cur, sel.firstChild.nextSibling); // วางถัดจาก placeholder
      }

      // เรียกโหลด staff เฉพาะกรณีที่มีเวลาเลือกได้จริงแล้ว
      if (found) loadStaff();
    })
    .catch(console.error);
}

function loadStaff() {
  const d = document.getElementById('booking_date').value;
  const t = document.getElementById('start_time').value; // จะเป็น '' ถ้ายังไม่ได้เลือก
  if (!d || !t || !TOTAL_MINUTES) return;

  fetch(`get_available_staff.php?date=${d}&start_time=${t}&duration=${TOTAL_MINUTES}`)
    .then(r => r.json())
    .then(list => {
      const sel = document.getElementById('staff_id');
      const currentStaff = "<?= (int)$booking['staff_id'] ?>";
      sel.innerHTML = '<option value="">เลือกผู้ให้บริการ</option>';
      list.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.staff_id;
        opt.textContent = s.staff_name;
        if (parseInt(s.staff_id) === parseInt(currentStaff)) opt.selected = true;
        sel.appendChild(opt);
      });
    })
    .catch(console.error);
}

document.addEventListener('DOMContentLoaded', function() {
  const dbDateStr = "<?= htmlspecialchars(substr($booking['booking_date'], 0, 10)) ?>";

  // สร้าง today / +3 เดือน แบบ local (ไม่ใช้ new Date("YYYY-MM-DD"))
  const now = new Date();
  const todayLocal = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const maxLocal   = new Date(now.getFullYear(), now.getMonth() + 3, now.getDate());
  const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

  const todayStr = fmt(todayLocal);
  const maxStr   = fmt(maxLocal);

  // เลือกค่าเริ่มต้นที่ใช้งานได้ในช่วง
  const initialStr = (dbDateStr && dbDateStr >= todayStr && dbDateStr <= maxStr) ? dbDateStr : todayStr;

  flatpickr("#booking_date", {
    dateFormat: "Y-m-d",
    defaultDate: initialStr,   // ใช้สตริงเท่านั้น
    minDate: todayStr,
    maxDate: maxStr,
    disableMobile: true,
    // inline: true, // ถ้าอยากให้ปฏิทินโชว์ตลอด
    onChange: (_, dateStr) => { if (dateStr) loadTimes(dateStr); }
  });

  // โหลดช่วงเวลาเริ่มต้นตาม initialStr
  loadTimes(initialStr);
  document.getElementById('start_time').addEventListener('change', loadStaff);
});
</script>
</html>
