<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID";
  exit;
}

$staff_id = intval($_GET['id']);

$sql = "SELECT * FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
  echo "ไม่พบข้อมูลพนักงาน";
  exit;
}

$imagePath = !empty($staff['st_profile']) ? 'assets/img/' . htmlspecialchars($staff['st_profile']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- คุณอาจใส่ meta, title, css ที่นี่ -->
</head>

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Edit Staff</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">

              <form class="row g-3" action="update_staff.php?id=<?= $staff['staff_id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="staff_id" value="<?= $staff['staff_id'] ?>">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingName" name="staff_name" value="<?= htmlspecialchars($staff['staff_name']) ?>" required>
                    <label for="floatingName">Name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" id="floatinggender" name="st_gender" required>
                      <option value="male" <?= $staff['st_gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                      <option value="female" <?= $staff['st_gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <label for="floatinggender">Gender</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="number" class="form-control" id="floatingAge" name="st_age" value="<?= htmlspecialchars($staff['st_age']) ?>" required>
                    <label for="floatingAge">Age</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingBirthday" name="st_birthday" value="<?= htmlspecialchars($staff['st_birthday']) ?>" required>
                    <label for="floatingBirthday">Birthday</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="floatingEmail" name="st_gmail" value="<?= htmlspecialchars($staff['st_gmail']) ?>" required>
                    <label for="floatingEmail">Email</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="floatingPhone" name="st_tel" value="<?= htmlspecialchars($staff['st_tel']) ?>" required>
                    <label for="floatingPhone">Phone</label>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-floating">
                    <textarea class="form-control" id="floatingAddress" name="st_address" style="height: 100px;"><?= htmlspecialchars($staff['st_address']) ?></textarea>
                    <label for="floatingAddress">Address</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingStartJob" name="start_job" value="<?= htmlspecialchars($staff['start_job']) ?>">
                    <label for="floatingStartJob">Start Job</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" id="floatingEndJob" name="end_job" value="<?= htmlspecialchars($staff['end_job']) ?>">
                    <label for="floatingEndJob">End Job</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" id="floatingStatus" name="st_status" required>
                      <option value="active" <?= $staff['st_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $staff['st_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <label for="floatingStatus">Status</label>
                  </div>
                </div>

                <div class="col-md-12">
  <label class="form-label">Upload Profile Image</label>
  <div class="upload-box" id="uploadBox">
    <div id="uploadText" style="<?= $imagePath ? 'display:none;' : 'display:block;' ?>">
      คลิกหรือลากไฟล์รูปภาพมาที่นี่
    </div>
    <input type="file" id="profileimg" name="st_profile" accept="image/*" />
    <img id="previewImage" src="<?= $imagePath ?>" style="<?= $imagePath ? 'display:block;' : 'display:none;' ?>" alt="Preview Image" />
  </div>
  <input type="hidden" name="old_image" value="<?= htmlspecialchars($staff['st_profile']) ?>">
</div>

<script>
  const uploadBox = document.getElementById('uploadBox');
  const fileInput = document.getElementById('profileimg');
  const previewImage = document.getElementById('previewImage');
  const uploadText = document.getElementById('uploadText');

  uploadBox.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadBox.classList.add('dragover');
  });

  uploadBox.addEventListener('dragleave', () => {
    uploadBox.classList.remove('dragover');
  });

  uploadBox.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadBox.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      showPreview(fileInput.files[0]);
    }
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      showPreview(fileInput.files[0]);
    }
  });

  function showPreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      previewImage.style.display = 'block';
      uploadText.style.display = 'none';
    }
    reader.readAsDataURL(file);
  }
</script>

                <div class="text-center mt-3">
                  <button type="submit" class="btn btn-primary">Update</button>
                  <a href="table_staff.php" class="btn btn-secondary">Cancel</a>
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
