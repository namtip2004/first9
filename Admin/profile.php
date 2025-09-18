

<?php
session_start(); 
// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

require_once("connect_db.php");

$staff_id  = $_SESSION['staff_id'];

// ดึงข้อมูล staff
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

$imagePath = !empty($staff['st_profile']) ? 'assets/img/' . htmlspecialchars($staff['st_profile']) : 'assets/img/profile-img.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Staff Profile - <?= htmlspecialchars($staff['staff_name']) ?></title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <style>
.upload-box {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.3s ease;
  position: relative;
  min-height: 250px; /* เพิ่ม min-height เพื่อรองรับรูปใหญ่ขึ้น */
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}

.upload-box img, #previewImage {
  max-width: 400px; /* ปรับจาก 300px เป็น 400px */
  max-height: 400px; /* ปรับจาก 300px เป็น 400px */
  border-radius: 8px;
  object-fit: cover;
}

    .upload-box:hover {
      border-color: #007bff;
    }

    .upload-box.dragover {
      border-color: #007bff;
      background-color: #f8f9fa;
    }



    .upload-box img {
      max-width: 300px;
      max-height: 300px;
      border-radius: 8px;
      object-fit: cover;
    }

    .upload-text {
      color: #666;
      font-size: 16px;
    }

    .info-item {
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f0f0f0;
    }

    .info-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 5px;
    }

    .info-value {
      color: #6c757d;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Staff Profile</h1>
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

              <img src="<?= $imagePath ?>" alt="Profile" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
              <h2><?= htmlspecialchars($staff['staff_name']) ?></h2>
              <h3><?= ucfirst(htmlspecialchars($staff['st_level'] ?? 'Staff')) ?></h3>
              <!-- <div class="mt-2">
                <?= $staff['st_status'] === 'active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>' ?>
              </div> -->
            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">

                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Reset Password</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                  <h5 class="card-title">Staff Information</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($staff['staff_name']) ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($staff['st_gmail']) ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($staff['st_tel']) ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Gender</div>
                    <div class="col-lg-9 col-md-8"><?= ucfirst(htmlspecialchars($staff['st_gender'])) ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Age</div>
                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($staff['st_age']) ?> years old</div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Birthday</div>
                    <div class="col-lg-9 col-md-8">
                      <?php 
                        if ($staff['st_birthday']) {
                          echo date('d/m/Y', strtotime($staff['st_birthday']));
                        } else {
                          echo 'ไม่ระบุ';
                        }
                      ?>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Address</div>
                    <div class="col-lg-9 col-md-8"><?= nl2br(htmlspecialchars($staff['st_address'])) ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Start Job</div>
                    <div class="col-lg-9 col-md-8">
                      <?php 
                        if ($staff['start_job']) {
                          echo date('d/m/Y', strtotime($staff['start_job']));
                        } else {
                          echo 'ไม่ระบุ';
                        }
                      ?>
                    </div>
                  </div>

                  <?php if ($staff['end_job']): ?>
                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">End Job</div>
                    <div class="col-lg-9 col-md-8"><?= date('d/m/Y', strtotime($staff['end_job'])) ?></div>
                  </div>
                  <?php endif; ?>
<!-- 
                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Role</div>
                    <div class="col-lg-9 col-md-8">
                      <span class="badge bg-primary"><?= ucfirst(htmlspecialchars($staff['st_level'] ?? 'Staff')) ?></span>
                    </div>
                  </div> -->

                  <!-- <div class="row">
                    <div class="col-lg-3 col-md-4 label">Status</div>
                    <div class="col-lg-9 col-md-8">
                      <?= $staff['st_status'] === 'active' 
                          ? '<span class="text-success fw-bold">Active</span>' 
                          : '<span class="text-danger fw-bold">Inactive</span>' ?>
                    </div>
                  </div> -->

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Staff ID</div>
                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($staff['staff_id']) ?></div>
                  </div>

                  <?php if (!empty($staff['st_profile'])): ?>
                  <div class="row mt-3">
                    <div class="col-lg-3 col-md-4 label">Profile Image</div>
                    <div class="col-lg-9 col-md-8">
                      <div style="width: 100px; height: 100px; overflow: hidden; border: 1px solid #ddd; border-radius: 8px; cursor: pointer;"
                           data-bs-toggle="modal" data-bs-target="#imageModal">
                        <img src="<?= $imagePath ?>" 
                             alt="Profile Image" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                      </div>
                      <small class="text-muted">คลิกเพื่อดูภาพขนาดใหญ่</small>
                    </div>
                  </div>

                  <!-- Modal for large image -->
                  <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="imageModalLabel">Profile Image</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                          <img src="<?= $imagePath ?>" alt="Large Profile Image" style="max-width: 100%; height: auto; max-height: 500px;">
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>

                  <!-- <div class="text-center mt-4">
                    <a href="table_staff.php" class="btn btn-secondary">กลับไปยังตารางพนักงาน</a>
                  </div> -->

                </div>


                  <!-- Profile Edit Form -->
                 <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
  <!-- Profile Edit Form -->
  <form action="update_staff.php?id=<?= $staff['staff_id'] ?>" method="POST" enctype="multipart/form-data">
    <!-- ลบ input hidden สำหรับ staff_id ออก เพราะส่งผ่าน URL แทน -->

                    <div class="row mb-3">
                      <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="upload-box" id="uploadBox">
                          <div class="upload-text" id="uploadText" style="<?= !empty($staff['st_profile']) ? 'display:none;' : '' ?>">
                            คลิกหรือลากไฟล์รูปภาพมาที่นี่
                          </div>
                          <input type="file" id="profileimg" name="st_profile" accept="image/*" />
                          <img 
                            id="previewImage"
                            src="<?= $imagePath ?>" 
                            style="<?= !empty($staff['st_profile']) ? 'display:block;' : 'display:none;' ?>" 
                            alt="Preview"
                          />
                        </div>
                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($staff['st_profile'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="staff_name" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="staff_name" type="text" class="form-control" id="staff_name" value="<?= htmlspecialchars($staff['staff_name']) ?>" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_gmail" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="st_gmail" type="email" class="form-control" id="st_gmail" value="<?= htmlspecialchars($staff['st_gmail']) ?>" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_tel" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="st_tel" type="text" class="form-control" id="st_tel" value="<?= htmlspecialchars($staff['st_tel']) ?>" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_gender" class="col-md-4 col-lg-3 col-form-label">Gender</label>
                      <div class="col-md-8 col-lg-9">
                        <select name="st_gender" class="form-select" id="st_gender">
                          <option value="male" <?= $staff['st_gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                          <option value="female" <?= $staff['st_gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_age" class="col-md-4 col-lg-3 col-form-label">Age</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="st_age" type="number" class="form-control" id="st_age" value="<?= htmlspecialchars($staff['st_age']) ?>" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_birthday" class="col-md-4 col-lg-3 col-form-label">Birthday</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="st_birthday" type="date" class="form-control" id="st_birthday" value="<?= htmlspecialchars($staff['st_birthday']) ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="st_address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                      <div class="col-md-8 col-lg-9">
                        <textarea name="st_address" class="form-control" id="st_address" rows="4"><?= htmlspecialchars($staff['st_address']) ?></textarea>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="start_job" class="col-md-4 col-lg-3 col-form-label">Start Job</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="start_job" type="date" class="form-control" id="start_job" value="<?= htmlspecialchars($staff['start_job']) ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="end_job" class="col-md-4 col-lg-3 col-form-label">End Job</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="end_job" type="date" class="form-control" id="end_job" value="<?= htmlspecialchars($staff['end_job'] ?? '') ?>">
                      </div>
                    </div>

                    <!-- <div class="row mb-3">
                      <label for="st_status" class="col-md-4 col-lg-3 col-form-label">Status</label>
                      <div class="col-md-8 col-lg-9">
                        <select name="st_status" class="form-select" id="st_status">
                          <option value="active" <?= $staff['st_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                          <option value="inactive" <?= $staff['st_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                      </div>
                    </div> -->

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                      <button type="button" class="btn btn-secondary" onclick="location.reload()">Cancel</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>

                <div class="tab-pane fade pt-3" id="profile-change-password">
                  <!-- Change Password Form -->
                  <form action="reset_staff_password.php" method="POST" id="resetPasswordForm">
                    <input type="hidden" name="staff_id" value="<?= $staff['staff_id'] ?>">

                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="new_password" type="password" class="form-control" id="newPassword" minlength="6" required>
                        <!-- <small class="text-muted">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small> -->
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="confirmPassword" class="col-md-4 col-lg-3 col-form-label">Confirm New Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="confirm_password" type="password" class="form-control" id="confirmPassword" minlength="6" required>
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-danger">Reset Password</button>
                    </div>
                  </form><!-- End Reset Password Form -->
                </div>

              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  

  <script>
    // JavaScript for drag and drop functionality
    const uploadBox = document.getElementById('uploadBox');
    const fileInput = document.getElementById('profileimg');
    const previewImage = document.getElementById('previewImage');
    const uploadText = document.getElementById('uploadText');

    uploadBox.addEventListener('click', () => {
    if (e.target !== fileInput && e.target !== previewImage) {
    fileInput.click();
  }
});

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

    // Password confirmation validation
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
        return false;
      }
      
      if (confirm('คุณแน่ใจหรือไม่ที่จะรีเซ็ตรหัสผ่านของพนักงานคนนี้?')) {
        return true;
      } else {
        e.preventDefault();
        return false;
      }
    });

    
  </script>

</body>

</html>