<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>
  
  <main id="main" class="main pt-4">

    <div class="pagetitle">
      <h1>Customer Table</h1>
      <nav>
        <ol class="breadcrumb"></ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">        
          <div class="card">
            <div class="card-body">
             <div class="text-end mb-2">
            <a href="form_member.php" class="btn btn-success mb-2">+ Member</a>
          </div>
              <?php 
                require_once("connect_db.php");
                $sql = "SELECT * FROM member";
                $result = mysqli_query($conn, $sql);
              ?>

              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>วันเกิด</th>
                    <th>เพศ</th>
                    <th>ที่อยู่</th>
                    <th>สถานะ</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $i = 1;
                    while($row = $result->fetch_assoc()) {
                  ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= htmlspecialchars($row['mb_first_name']) ?></td>
                      <td><?= htmlspecialchars($row['mb_last_name']) ?></td>
                      <td><?= htmlspecialchars($row['mb_gmail']) ?></td>
                      <td><?= htmlspecialchars($row['mb_tel']) ?></td>
                      <td><?= htmlspecialchars($row['mb_birthday']) ?></td>
                      <td><?= htmlspecialchars($row['mb_gender']) ?></td>
                      <td><?= htmlspecialchars($row['mb_address']) ?></td>
                      <td><?= htmlspecialchars($row['mb_status']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="forms_update_member.php?id=<?= $row['member_ID'] ?>">แก้ไข</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" 
                           href="delete_member.php?id=<?= $row['member_ID'] ?>" 
                           onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสมาชิกนี้?');">ลบ</a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>

              <?php mysqli_close($conn); ?>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>
</body>
</html>
