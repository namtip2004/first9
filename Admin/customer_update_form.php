<!DOCTYPE html>
<html lang="en">
<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID";
  exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM customer WHERE customer_id = '$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "ไม่พบข้อมูลลูกค้า";
  exit;
}

$imagePath = !empty($data['profileimg']) ? 'assets/img/' . htmlspecialchars($data['profileimg']) : '';
?>

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Edit Customer</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <form action="update_customer.php" method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="customer_id" value="<?= $data['customer_id'] ?>">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="customer_name" value="<?= $data['customer_name'] ?>" required>
                    <label for="customer_name">Customer Name</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" name="gmail" value="<?= $data['gmail'] ?>" required>
                    <label for="gmail">Email</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="tel" value="<?= $data['tel'] ?>" required>
                    <label for="tel">Phone</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="date" class="form-control" name="birthday" value="<?= $data['birthday'] ?>" required>
                    <label for="birthday">Birthday</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <select class="form-select" name="gender">
                      <option value="Male" <?= $data['gender'] === '?Male' ? 'selected' : '' ?>>Male</option>
                      <option value="Female" <?= $data['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <label for="gender">Gender</label>
                  </div>
                </div>

                <div class="col-md-6">
  <div class="form-floating">
    <select class="form-select" id="status" name="status" required>
      <option value="active"   <?= (isset($data['account_status']) && strtolower($data['account_status'])==='active')   ? 'selected' : '' ?>>Active</option>
      <option value="inactive" <?= (isset($data['account_status']) && strtolower($data['account_status'])==='inactive') ? 'selected' : '' ?>>Inactive</option>
    </select>
    <label for="status">Account Status</label>
  </div>
</div>



                <div class="col-md-12">
                  <label class="form-label">Upload Profile Image</label>
                  <div class="upload-box" id="uploadBox">
                    <div class="upload-text" id="uploadText" style="<?= $imagePath ? 'display:none;' : '' ?>">
                      คลิกหรือลากไฟล์รูปภาพมาที่นี่
                    </div>
                    <input type="file" id="profileimg" name="profileimg" accept="image/*" />
                    <img 
                      id="previewImage"
                      src="<?= $imagePath ?>" 
                      style="<?= $imagePath ? 'display:block;' : 'display:none;' ?>" 
                      alt="Preview"
                    />
                  </div>
                  <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['profileimg']) ?>">
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

                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
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
