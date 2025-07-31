<!DOCTYPE html>
<html lang="en">


<body>
  <?php include("header.php"); ?>
  <?php include("slidebar.php"); ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Service From</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title"></h5> -->
              <form action="insert_service.php" method="POST" class="row g-3">

                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" name="course_name" placeholder="service" required>
                    <label for="course_name">Service Name</label>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-floating">
                    <textarea class="form-control" name="course_detail" placeholder="Description" style="height: 100px" required></textarea>
                    <label for="course_detail">Description</label>
                  </div>
                </div>

                <!-- <div class="col-md-6">
                  <div class="form-floating">
                    <input type="number" step="0.01" class="form-control" name="active_status" placeholder="active status" required>
                    <label for="active_status">active status</label>
                  </div>
                </div> -->

                <div class="col-md-6">
                  <div class="form-check form-switch mt-3">
                       <input type="hidden" name="active_status" value="0">
                        <input class="form-check-input" type="checkbox" name="active_status" value="1" id="active_status">
                        <label class="form-check-label" for="active_status">Active Status</label>
                  </div>
                </div>

               <!-- Tag Cloud -->
<div class="col-md-12">
  <label for="tagInput" class="form-label">Tags</label>
  <div class="position-relative">
    <input type="text" id="tagInput" class="form-control" placeholder="Type to search or add tag...">
    <div class="autocomplete-suggestions" id="suggestionBox" hidden></div>
  </div>
  <div class="tag-box mt-2" id="tagsDisplay"></div>
  <input type="hidden" name="tags" id="tagsHidden">
</div>
                <!-- ฟอร์มเวลาเพิ่มเติม -->

<!-- อยู่ภายใน <form> เหมือนเดิม -->
<div class="col-md-12 mb-2">  
  <div class="d-flex align-items-center">
    <h5 class="mb-0 me-2">time option</h5>
    <button type="button" class="btn btn-secondary btn-sm" onclick="addTimePriceField()">+ add</button>
  </div>
</div>

<div class="col-md-12" id="new-time-price">
  <div class="row mb-2">
    <div class="col-md-3">
      <div class="input-group">
        <input type="number" name="new_times[]" class="form-control" required min="0" placeholder="">
        <span class="input-group-text">minute</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="input-group">
        <input type="number" name="new_prices[]" class="form-control" required min="0" step="0.01" placeholder="">
        <span class="input-group-text">€</span>
      </div>
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Delete</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
  <script>

function addTimePriceField() {
  const container = document.getElementById('new-time-price');
  const html = `
    <div class="row mb-2">
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="new_times[]" class="form-control" required min="0" placeholder="">
          <span class="input-group-text">minute</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="new_prices[]" class="form-control" required min="0" step="0.01" placeholder="">
          <span class="input-group-text">€</span>
        </div>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">Delete</button>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}
</script>
<script>
  const tagInput = document.getElementById('tagInput');
  const suggestionBox = document.getElementById('suggestionBox');
  const tagsDisplay = document.getElementById('tagsDisplay');
  const tagsHidden = document.getElementById('tagsHidden');
  const tags = [];

  tagInput.addEventListener('input', async () => {
    const query = tagInput.value.trim();
    if (query === '') {
      suggestionBox.hidden = true;
      return;
    }

    const res = await fetch(`tag_input.php?q=${encodeURIComponent(query)}`);
    const results = await res.json();
    showSuggestions(results);
  });

  tagInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      addTag(tagInput.value.trim());
      suggestionBox.hidden = true;
    }
  });

  function showSuggestions(results) {
    suggestionBox.innerHTML = '';
    results.forEach(tag => {
      const div = document.createElement('div');
      div.textContent = tag.tag_name;
      div.onclick = () => {
        addTag(tag.tag_name);
        suggestionBox.innerHTML = '';
        suggestionBox.hidden = true;
      };
      suggestionBox.appendChild(div);
    });
    suggestionBox.hidden = results.length === 0;
  }

  function addTag(text) {
    const cleaned = text.trim();
    if (cleaned && !tags.includes(cleaned)) {
      tags.push(cleaned);
      const tagEl = document.createElement('span');
      tagEl.classList.add('tag');
      tagEl.textContent = cleaned;

      const removeBtn = document.createElement('span');
      removeBtn.classList.add('remove-tag');
      removeBtn.innerHTML = '&times;';
      removeBtn.onclick = () => removeTag(cleaned);

      tagEl.appendChild(removeBtn);
      tagsDisplay.appendChild(tagEl);

      updateHiddenInput();
    }
    tagInput.value = '';
  }

  function removeTag(text) {
    const index = tags.indexOf(text);
    if (index > -1) {
      tags.splice(index, 1);
      const tagElements = document.querySelectorAll('.tag');
      tagElements.forEach(tag => {
        if (tag.textContent.includes(text)) tag.remove();
      });
      updateHiddenInput();
    }
  }

  function updateHiddenInput() {
    tagsHidden.value = tags.join(',');
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
