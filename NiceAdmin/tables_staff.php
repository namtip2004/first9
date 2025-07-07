<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Staff Table</h1>
      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Staff List</h5>

              <?php 
              require_once("connect_db.php");
              $sql = "SELECT * FROM staff";
              $result = mysqli_query($conn, $sql);
              ?>

              <!-- Table -->
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>เพศ</th>
                    <th>อายุ</th>
                    <th>สัญชาติ</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>ที่อยู่</th>
                    <th>วันเริ่มงาน</th>
                    <th>วันสิ้นสุดการทำงาน</th>
                    <th>สถานะ</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?= htmlspecialchars($row['staff_F_name']) ?></td>
                      <td><?= htmlspecialchars($row['staff_L_name']) ?></td>
                      <td><?= htmlspecialchars($row['staff_gender']) ?></td>
                      <td><?= htmlspecialchars($row['staff_age']) ?></td>
                      <td><?= htmlspecialchars($row['staff_nationality']) ?></td>
                      <td><?= htmlspecialchars($row['staff_mail']) ?></td>
                      <td><?= htmlspecialchars($row['staff_tel']) ?></td>
                      <td><?= htmlspecialchars($row['staff_address']) ?></td>
                      <td><?= htmlspecialchars($row['start_job']) ?></td>
                      <td><?= htmlspecialchars($row['end_job']) ?></td>
                      <td><?= htmlspecialchars($row['staff_status']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="forms_update_staff.php?id=<?= $row['staff_ID'] ?>">แก้ไข</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" href="delete_staff.php?id=<?= $row['staff_ID'] ?>" onclick="return confirm('ยืนยันการลบข้อมูลพนักงาน?');">ลบ</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <!-- End Table -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>
</body>

</html>
