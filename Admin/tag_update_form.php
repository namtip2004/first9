<?php
session_start();
require_once("connect_db.php");

if (!isset($_GET['id'])) {
    header("Location: tags_table.php");
    exit;
}

$tag_id = (int)$_GET['id'];

// ดึงข้อมูล tag
$sql_tag = "SELECT * FROM tag WHERE tag_id = ?";
$stmt = $conn->prepare($sql_tag);
$stmt->bind_param("i", $tag_id);
$stmt->execute();
$result_tag = $stmt->get_result();

if ($result_tag->num_rows === 0) {
    // ถ้าไม่มี tag นี้
    $_SESSION['error'] = "Tag not found.";
    header("Location: tags_table.php");
    exit;
}

$tag = $result_tag->fetch_assoc();
$stmt->close();

// ดึง service ทั้งหมด
$sql_services = "SELECT service_id, service_name FROM service";
$result_services = mysqli_query($conn, $sql_services);

// ดึง service ที่ tag นี้ใช้ (tag_service)
$sql_tag_services = "SELECT service_id FROM tag_service WHERE tag_id = ?";
$stmt2 = $conn->prepare($sql_tag_services);
$stmt2->bind_param("i", $tag_id);
$stmt2->execute();
$result_tag_services = $stmt2->get_result();

$selected_services = [];
while ($row = $result_tag_services->fetch_assoc()) {
    $selected_services[] = $row['service_id'];
}
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Tag</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container--default .select2-selection--multiple {
      min-height: 50px;
      padding: 6px;
    }
  </style>
</head>
<body>

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Update Tag</h1>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body pt-4">

            <form class="row g-3" action="update_tag.php" method="POST">
              <input type="hidden" name="tag_id" value="<?= $tag_id ?>">

              <!-- Tag Name -->
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="tag_name" name="tag_name" placeholder="Tag Name" required
                    value="<?= htmlspecialchars($tag['tag_name']) ?>">
                  <label for="tag_name">Tag Name</label>
                </div>
              </div>

              <!-- Service Selection -->
              <div class="col-md-12">
                <label for="services" class="form-label">Select Services</label>
                <select class="form-control select2" id="services" name="services[]" multiple="multiple" style="width: 100%;" required>
                  <?php
                    while ($service = mysqli_fetch_assoc($result_services)) {
                      $selected = in_array($service['service_id'], $selected_services) ? 'selected' : '';
                      echo '<option value="' . $service['service_id'] . '" ' . $selected . '>' . htmlspecialchars($service['service_name']) . '</option>';
                    }
                  ?>
                </select>
              </div>

              <!-- Submit -->
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="table_tags.php" class="btn btn-secondary">Cancel</a>
              </div>

            </form>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Search and select services",
      allowClear: true
    });
  });
</script>

</body>
</html>
