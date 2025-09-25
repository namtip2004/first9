<?php
session_start();

// ตรวจสอบว่า staff_level มีอยู่ในเซสชันหรือไม่
if (isset($_SESSION['staff_level']) && $_SESSION['staff_level'] !== 'admin') {
    header('Location: profile.php');
    exit;
}

require_once("connect_db.php");

$staffMembers      = [];
$staffQueryError   = null;
$staffQuery        = "SELECT * FROM staff WHERE st_level != 'admin' ORDER BY staff_name";
$staffQueryResult  = mysqli_query($conn, $staffQuery);

if ($staffQueryResult) {
    while ($row = mysqli_fetch_assoc($staffQueryResult)) {
        $staffMembers[] = $row;
    }
} else {
    $staffQueryError = mysqli_error($conn);
}

?>

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
              <!-- <h5 class="card-title">Staff</h5> -->
              <div class="text-end mb-2">
                <a href="form_staff.php" class="btn btn-success mb-2">+ add staff</a>
              </div>

              <?php if ($staffQueryError): ?>
                <div class="alert alert-danger" role="alert">
                  <?= htmlspecialchars($staffQueryError); ?>
                </div>
              <?php endif; ?>

              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NO.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Birthday</th>
                    <th>Gmail</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Start Job</th>
                    <th>End Job</th>
                    <th>Status</th>
                    <th>Schedule</th>
                    <th>Detail</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($staffMembers)): ?>
                    <?php foreach ($staffMembers as $index => $staff): ?>
                      <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($staff['staff_name']); ?></td>
                        <td><?= htmlspecialchars($staff['st_gender']); ?></td>
                        <td><?= htmlspecialchars($staff['st_age']); ?></td>
                        <td><?= htmlspecialchars($staff['st_birthday']); ?></td>
                        <td><?= htmlspecialchars($staff['st_gmail']); ?></td>
                        <td><?= htmlspecialchars($staff['st_tel']); ?></td>
                        <td><?= htmlspecialchars($staff['st_address']); ?></td>
                        <td><?= htmlspecialchars($staff['start_job']); ?></td>
                        <td><?= htmlspecialchars($staff['end_job']); ?></td>
                        <td><?= htmlspecialchars($staff['st_status']); ?></td>
                        <td>
                          <a class="btn btn-outline-info btn-sm"
                             href="staff_schedule.php?staff_id=<?= (int) $staff['staff_id']; ?>">
                            Schedule
                          </a>
                        </td>
                        <td>
                          <a class="btn btn-outline-primary btn-sm" href="staff_detail.php?id=<?= $staff['staff_id']; ?>">Detail</a>
                        </td>
                        <td>
                          <a class="btn btn-outline-primary btn-sm" href="staff_update_form.php?id=<?= $staff['staff_id']; ?>">Edit</a>
                        </td>
                        <td>
                          <a class="btn btn-outline-danger btn-sm" href="staff_delete.php?id=<?= $staff['staff_id']; ?>" onclick="return confirm('Are you sure you want to permanently delete this staff\'s data?');">Delete</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="15" class="text-center text-muted">No staff members found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <?php include("footer.php"); ?>
</body>

</html>
