<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID คอร์ส";
  exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM course WHERE course_ID = '$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "ไม่พบข้อมูลคอร์ส";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
     <?php
require_once("connect_db.php");
$id = $_GET['id'];
$sql = "SELECT * FROM course WHERE course_ID = '$id'";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);
?>

<h2>แก้ไขคอร์ส</h2>
<form method="POST" action="update_course_db.php">
  <input type="hidden" name="course_ID" value="<?= $data['course_ID'] ?>">

  <label>ชื่อคอร์ส</label>
  <input type="text" name="course_name" value="<?= $data['course_name'] ?>" class="form-control" required>

  <label>รายละเอียด</label>
  <textarea name="course_detail" class="form-control"><?= $data['course_detail'] ?></textarea>

  <label>ราคา</label>
  <input type="number" name="course_price" value="<?= $data['course_price'] ?>" class="form-control" step="0.01">

  <h5>เวลาเรียนเดิม</h5>
  <?php
  $sql_time = "SELECT * FROM time WHERE course_ID = '$id'";
  $res_time = mysqli_query($conn, $sql_time);
  while ($time = mysqli_fetch_assoc($res_time)) {
  ?>
    <div class="input-group mb-2">
      <input type="time" name="existing_times[<?= $time['time_ID'] ?>]" value="<?= $time['Time'] ?>" class="form-control">
      <a href="delete_time.php?id=<?= $time['time_ID'] ?>&course=<?= $id ?>" class="btn btn-danger">ลบ</a>
    </div>
  <?php } ?>

  <h5>เพิ่มเวลาใหม่</h5>
  <div id="new-times">
    <div class="input-group mb-2">
      <input type="time" name="new_times[]" class="form-control">
      <button type="button" class="btn btn-secondary" onclick="addTimeField()">+</button>
    </div>
  </div>

  <button type="submit" class="btn btn-primary mt-3">บันทึกการแก้ไข</button>
</form>

<script>
function addTimeField() {
  document.getElementById('new-times').insertAdjacentHTML('beforeend', `
    <div class="input-group mb-2">
      <input type="time" name="new_times[]" class="form-control">
      <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">ลบ</button>
    </div>
  `);
}
</script>


  </main>
  <?php include("footer.php"); ?>
</body>
</html>
