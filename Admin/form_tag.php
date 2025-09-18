<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tags Form</title>

  <!-- ✅ Bootstrap CSS (Optional for styling) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Select2 CSS -->
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
    <h1>Tags Form</h1>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body pt-4">

            <form class="row g-3" action="insert_tags.php" method="POST">

              <!-- Tag Name -->
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="floatingtags" name="floatingtags" placeholder="Tag Name" required>
                  <label for="floatingtags">Tag Name</label>
                </div>
              </div>

              <!-- Service Selection with Search -->
              <div class="col-md-12">
                <label for="services" class="form-label">Select Services</label>
                <select class="form-control select2" id="services" name="services[]" multiple="multiple" style="width: 100%;" required>
                  <?php
                    include("connect_db.php");
                    $query = "SELECT service_id, service_name FROM service";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo '<option value="' . $row['service_id'] . '">' . htmlspecialchars($row['service_name']) . '</option>';
                    }
                  ?>
                </select>
              </div>

              <!-- Submit -->
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">Submit</button>
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

<!-- ✅ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- ✅ Bootstrap JS (Optional for UI) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Init Select2 -->
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
