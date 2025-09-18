<?php
require_once("connect_db.php");

if (!isset($_GET['id'])) {
  echo "ไม่พบ ID";
  exit;
}

$customer_id = $_GET['id'];

// ดึงข้อมูล customer
$sql = "SELECT * FROM customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
  echo "ไม่พบข้อมูลลูกค้า";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include("header.php"); ?>
<?php include("slidebar.php"); ?>

<main id="main" class="main pt-5 mt-5">
  <div class="pagetitle">
    <h1>Customer Detail</h1>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body pt-4">

            <h4><?= htmlspecialchars($customer['customer_name']) ?></h4>

            <p><strong>Email:</strong> <?= htmlspecialchars($customer['gmail']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($customer['tel']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($customer['gender']) ?></p>
            <p><strong>Birthday:</strong> <?= htmlspecialchars($customer['birthday']) ?></p>
            <p><strong>Status:</strong> 
              <?= $customer['account_status'] === 'active' 
                  ? '<span class="text-success">Active</span>' 
                  : '<span class="text-danger">Inactive</span>' ?>
            </p>

<?php if (!empty($customer['profileimg'])): ?>
  <div class="my-3">
    <h5>Profile Image</h5>
    <div style="width: 150px; height: 150px; overflow: hidden; border: 1px solid #ccc; border-radius: 8px; cursor: pointer;"
         data-bs-toggle="modal" data-bs-target="#imageModal">
      <img src="assets/img/<?= htmlspecialchars($customer['profileimg']) ?>" 
           alt="Profile Image" 
           style="width: 100%; height: 100%; object-fit: contain;">
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-body p-0 d-flex justify-content-center align-items-center" style="min-height: 300px;">
          <img src="assets/img/<?= htmlspecialchars($customer['profileimg']) ?>" alt="Large Image" style="max-width: 300px; height: auto;">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <p class="text-muted">No profile image uploaded.</p>
<?php endif; ?>

            <div class="text-center mt-4">
              <a href="customer_update_form.php?id=<?= $customer['customer_id'] ?>" class="btn btn-primary mt-3">Edit</a>
              <a href="table_customer.php" class="btn btn-secondary mt-3">Back</a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>
</body>
</html>
