<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>
  
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Member Table</h1>
      <nav>
        <ol class="breadcrumb">
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">couse</h5>
              


              <!-- Table with stripped rows -->
             <?php
require_once("connect_db.php");

$sql = "SELECT * FROM course";
$result = mysqli_query($conn, $sql);

// ตรวจสอบว่าดึงข้อมูลได้ไหม
if (!$result) {
    die("Query Error: " . mysqli_error($conn));  // แสดง error ชัด ๆ แล้วหยุด
}
?>

<div class="text-end mb-2">
  <a href="forms_couse.php" class="btn btn-success">+ course</a>
</div>


<table class="table table-bordered">
  <thead>
    <tr>
      <th>ชื่อคอร์ส</th>
      <th>รายละเอียด</th>
      <th>ราคา</th>
      <th>เวลาเรียน</th>
      <th>แก้ไข</th>
      <th>ลบ</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= htmlspecialchars($row['course_name']) ?></td>
        <td><?= htmlspecialchars($row['course_detail']) ?></td>
        <td><?= number_format($row['course_price'], 2) ?></td>
        <td>
          <?php
          $course_id = $row['course_ID'];
          $sql_time = "SELECT * FROM time WHERE course_ID = $course_id";
          $res_time = mysqli_query($conn, $sql_time);
          if ($res_time) {
            while ($time = mysqli_fetch_assoc($res_time)) {
              echo "<div>" . htmlspecialchars($time['Time']) . "</div>";
            }
          } else {
            echo "<div class='text-danger'>ดึงเวลาไม่สำเร็จ</div>";
          }
          ?>
        </td>
        <td>
            <a class="btn btn-outline-primary btn-sm" href="form_update_course.php?id=<?= $row['course_ID'] ?>">แก้ไข</a>
        </td>
        <td>
            <a class="btn btn-outline-danger btn-sm" href="delete_course.php?id=<?= $row['course_ID'] ?>" onclick="return confirm('ยืนยันการลบข้อมูลพนักงาน?');">ลบ</a>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>


              <!-- End Table with stripped rows -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>

</body>
</html>
