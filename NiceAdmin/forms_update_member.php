<?php
require_once("connect_db.php");

// ตรวจสอบว่ามี id หรือไม่
if (!isset($_GET['id'])) {
  echo "ไม่พบรหัสสมาชิก";
  exit;
}

$id = $_GET['id'];

// ดึงข้อมูลสมาชิกจากฐานข้อมูล
$sql = "SELECT * FROM member WHERE member_id = '$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

// ถ้าไม่เจอ
if (!$data) {
  echo "ไม่พบข้อมูลสมาชิก";
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
      <h1>update member</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>
                
              <form action="update_member.php" method="POST" class="row g-3">
                <input type="hidden" name="member_id" value="<?= $data['member_ID'] ?>">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="mb_first_name" value="<?= $data['mb_first_name'] ?>" required>
                    <label>ชื่อ</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="mb_last_name" value="<?= $data['mb_last_name'] ?>" required>
                    <label>นามสกุล</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" name="mb_gmail" value="<?= $data['mb_gmail'] ?>" required>
                    <label>อีเมล</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="mb_tel" value="<?= $data['mb_tel'] ?>">
                    <label>เบอร์โทร</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" name="mb_birthday" value="<?= $data['mb_birthday'] ?>">
                    <label>วันเกิด</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="mb_gender" value="<?= $data['mb_gender'] ?>">
                    <label>เพศ</label>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-floating">
                    <textarea class="form-control" name="mb_address" style="height: 100px"><?= $data['mb_address'] ?></textarea>
                    <label>ที่อยู่</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" name="mb_status">
                      <option value="active" <?= $data['mb_status'] == 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $data['mb_status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <label>สถานะ</label>
                  </div>
                </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary">Update</button>
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
