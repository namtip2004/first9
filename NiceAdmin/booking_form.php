<?php
session_start();
include("header.php");
include("slidebar.php");
include("connect_db.php"); // เชื่อมต่อฐานข้อมูล
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Booking Form</h1>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Book a Massage</h5>

            <form class="row g-3" id="bookingForm" action="insert_booking.php" method="POST">
              <div class="col-md-6">
                <label for="booking_date" class="form-label">Select Date</label>
                <input type="date" class="form-control" name="booking_date" id="booking_date" required>
              </div>

              <div class="col-md-6">
                <label for="time_start" class="form-label">Start Time</label>
                <input type="time" class="form-control" name="time_start" id="time_start" required>
              </div>

              <div class="col-md-12">
                <label for="courses" class="form-label">Select Courses</label>
                <select class="form-select" name="courses[]" id="courses" multiple required>
                  <?php
                  $query = mysqli_query($conn, "SELECT c.course_ID, c.course_name, t.Time FROM course c JOIN time t ON c.course_ID = t.course_ID");
                  while ($row = mysqli_fetch_assoc($query)) {
                    echo '<option value="' . $row['course_ID'] . '" data-duration="' . $row['Time'] . '">' . $row['course_name'] . ' (' . $row['Time'] . ')</option>';
                  }
                  ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="time_total" class="form-label">Total Duration</label>
                <input type="text" class="form-control" name="time_total" id="time_total" readonly>
              </div>

              <div class="col-md-6">
                <label for="staff_ID" class="form-label">Available Staff</label>
                <select class="form-select" name="staff_ID" id="staff_ID" required>
                  <option value="">-- Select Staff --</option>
                </select>
              </div>

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

<script>
const courseSelect = document.getElementById('courses');
const timeTotalInput = document.getElementById('time_total');
const staffSelect = document.getElementById('staff_ID');

function calculateTotalTime() {
  let totalMinutes = 0;
  Array.from(courseSelect.selectedOptions).forEach(opt => {
    const duration = opt.getAttribute('data-duration');
    const [h, m, s] = duration.split(":").map(Number);
    totalMinutes += h * 60 + m;
  });
  const hrs = Math.floor(totalMinutes / 60).toString().padStart(2, '0');
  const mins = (totalMinutes % 60).toString().padStart(2, '0');
  timeTotalInput.value = `${hrs}:${mins}:00`;
  fetchAvailableStaff();
}

function fetchAvailableStaff() {
  const date = document.getElementById('booking_date').value;
  const time = document.getElementById('time_start').value;
  const duration = timeTotalInput.value;
  if (date && time && duration) {
    fetch(`api/available_staff.php?date=${date}&time=${time}&duration=${duration}`)
      .then(res => res.json())
      .then(data => {
        staffSelect.innerHTML = '<option value="">-- Select Staff --</option>';
        data.forEach(staff => {
          const opt = document.createElement('option');
          opt.value = staff.staff_ID;
          opt.textContent = staff.staff_F_name + ' ' + staff.staff_L_name;
          staffSelect.appendChild(opt);
        });
      });
  }
}

courseSelect.addEventListener('change', calculateTotalTime);
document.getElementById('booking_date').addEventListener('change', fetchAvailableStaff);
document.getElementById('time_start').addEventListener('change', fetchAvailableStaff);
</script>

<?php include("footer.php"); ?>
