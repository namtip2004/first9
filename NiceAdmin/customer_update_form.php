<?php
require_once("connect_db.php");

// ตรวจสอบว่ามี id หรือไม่
if (!isset($_GET['id'])) {
  echo "ไม่พบรหัสสมาชิก";
  exit;
}

$id = $_GET['id'];

// ดึงข้อมูลสมาชิกจากฐานข้อมูล
$sql = "SELECT * FROM customer WHERE customer_id = '$id'";
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
      <h1>Update Customer</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
                <!-- <h5 class="card-title"></h5> -->
                
              <form action="update_customer.php" method="POST" class="row g-3">
                <input type="hidden" name="customer_id" value="<?= $data['customer_id'] ?>">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="customer_name" value="<?= $data['customer_name'] ?>">
                    <label>Name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="gender" value="<?= $data['gender'] ?>">
                    <label>Gender</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" name="birthday" value="<?= $data['birthday'] ?>">
                    <label>Birthday</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" name="gmail" value="<?= $data['gmail'] ?>" required>
                    <label>Gmail</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="tel" value="<?= $data['tel'] ?>">
                    <label>Phone number</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" name="account_status">
                      <option value="active" <?= $data['account_status'] == 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $data['account_status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <label>status</label>
                  </div>
                </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="table_customer.php" class="btn btn-secondary">Cancel</a>
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
