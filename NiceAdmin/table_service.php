<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>
  
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Service Table</h1>
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
              <!-- <h5 class="card-title">couse</h5> -->
              


              <!-- Table with stripped rows -->
             <?php
require_once("connect_db.php");

$sql = "SELECT * FROM service";
$result = mysqli_query($conn, $sql);

// ตรวจสอบว่าดึงข้อมูลได้ไหม
if (!$result) {
    die("Query Error: " . mysqli_error($conn));  // แสดง error ชัด ๆ แล้วหยุด
}
?>

<div class="text-end mb-2">
  <a href="form_service.php" class="btn btn-success">+ add service</a>
</div>


<table class="table table-bordered">
  <thead>
    <tr>
      <th>NO.</th>
      <th>service Name</th>
      <th>description</th>
      <th>status</th>
      <th>Detail</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['service_name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= number_format($row['is_active']) ?></td>
                <td>
            <a class="btn btn-outline-primary btn-sm" href="service_detail.php?id=<?= $row['service_id'] ?>">Detail</a>
        </td>
        <td>
            <a class="btn btn-outline-primary btn-sm" href="service_update_from.php?id=<?= $row['service_id'] ?>">Edit</a>
        </td>
        <td>
            <a class="btn btn-outline-danger btn-sm" href="service_delete.php?id=<?= $row['service_id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this service\'s data?');">Delete</a>
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
