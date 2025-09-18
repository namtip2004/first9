<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Tags Table</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Tags Table</h1>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                <form method="GET" class="d-flex align-items-center">
                  <label for="filter" class="me-2">Filter:</label>
                  <select name="filter" id="filter" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: auto;">
                    <option value="">-- All --</option>
                    <option value="1" <?= isset($_GET['filter']) && $_GET['filter'] === '1' ? 'selected' : '' ?>>✅ In use</option>
                    <option value="0" <?= isset($_GET['filter']) && $_GET['filter'] === '0' ? 'selected' : '' ?>>❌ Not use</option>
                  </select>
                </form>

                <a href="form_tag.php" class="btn btn-success">+ Add tag</a>
              </div>

              <?php 
              require_once("connect_db.php");

              // Get filter value
              $filter = isset($_GET['filter']) ? $_GET['filter'] : '';

              // SQL query
              $sql = "
                SELECT t.*, 
                       CASE 
                         WHEN ts.tag_id IS NOT NULL THEN 1 
                         ELSE 0 
                       END AS in_use
                FROM tag t
                LEFT JOIN (
                    SELECT DISTINCT tag_id FROM tag_service
                ) ts ON t.tag_id = ts.tag_id
              ";

              // Add WHERE clause if filter is set
              if ($filter !== '') {
                  $sql .= " WHERE " . ($filter == '1' ? "ts.tag_id IS NOT NULL" : "ts.tag_id IS NULL");
              }

              $result = mysqli_query($conn, $sql);

              if (!$result) {
                  echo "<div class='alert alert-danger'>Query Failed: " . mysqli_error($conn) . "</div>";
              } else {
              ?>

              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Tag Name</th>
                    <th>In use</th>
                    <th>Detail</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $j = 1;
                  while ($tag = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?= $j++ ?></td>
                      <td><?= htmlspecialchars($tag['tag_name']) ?></td>
                      <td><?= $tag['in_use'] ? '✅ in use' : '❌ not use' ?></td>
                      <td>
  <button class="btn btn-outline-primary btn-sm btn-detail" data-tag-id="<?= $tag['tag_id'] ?>">Detail</button>

</td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="tag_update_form.php?id=<?= $tag['tag_id'] ?>">Edit</a>
                      </td>
                      <td>
  <a class="btn btn-outline-danger btn-sm" 
     href="tag_delete.php?id=<?= $tag['tag_id'] ?>"
     onclick="return confirm('<?= $tag['in_use'] ? 'This tag is in use. Deleting it will remove it from all related services. Are you sure?' : 'Are you sure you want to delete this tag?' ?>');">
     Delete
  </a>
</td>

                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <?php } ?>

            </div>
          </div>
        </div>
      </div>
    </section>
  </main><!-- End #main -->

  <?php include("footer.php"); ?>
  <!-- Popup modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Services Using This Tag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalBodyContent">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const detailButtons = document.querySelectorAll('.btn-detail');
  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  const modalBody = document.getElementById('modalBodyContent');

  detailButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tagId = btn.getAttribute('data-tag-id');
      modalBody.innerHTML = 'Loading...';

      fetch(`tag_detail_services.php?tag_id=${tagId}`)
        .then(response => response.json())
        .then(data => {
          if(data.length === 0){
            modalBody.innerHTML = '<p>No services use this tag.</p>';
            return;
          }

          let html = '<ul class="list-group">';
          data.forEach(service => {
            html += `<li class="list-group-item">${service.service_name}</li>`;
          });
          html += '</ul>';

          modalBody.innerHTML = html;
        })
        .catch(err => {
          modalBody.innerHTML = `<p class="text-danger">Error loading data.</p>`;
          console.error(err);
        });

      modal.show();
    });
  });
});
</script>

</body>

</html>
