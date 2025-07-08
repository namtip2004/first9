<!DOCTYPE html>
<html lang="en">

<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>เพิ่มข้อมูลคอร์ส</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"></h5>
              <form action="insert_course.php" method="POST" class="row g-3">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="course_name" placeholder="ชื่อคอร์ส" required>
                    <label for="course_name">ชื่อคอร์ส</label>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-floating">
                    <textarea class="form-control" name="course_detail" placeholder="รายละเอียดคอร์ส" style="height: 100px" required></textarea>
                    <label for="course_detail">รายละเอียดคอร์ส</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="number" step="0.01" class="form-control" name="course_price" placeholder="ราคา" required>
                    <label for="course_price">ราคา</label>
                  </div>
                </div>
                <!-- ฟอร์มเวลาเพิ่มเติม -->

<!-- อยู่ภายใน <form> เหมือนเดิม -->
<h5>เพิ่มเวลาเรียน</h5>
<div class="col-md-6" id="new-times">
  <div class="input-group mb-2">
    <input type="time" name="new_times[]" class="form-control" required>
    <button type="button" class="btn btn-secondary" onclick="addTimeField()">+</button>
  </div>
</div>

<script>
function addTimeField() {
  const container = document.getElementById('new-times');
  const html = `
    <div class="input-group mb-2">
      <input type="time" name="new_times[]" class="form-control" required>
      <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">ลบ</button>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}
</script>

                <div class="text-center">
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
</body>

</html>
