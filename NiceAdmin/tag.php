<?php
// tag_input.php

// ✅ เชื่อมต่อฐานข้อมูล
$conn = new mysqli("127.0.0.1", "root", "", "first9");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ หากมีการร้องขอผ่าน AJAX (API)
if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT tag_id, tag_name FROM tag WHERE tag_name LIKE '%$q%' LIMIT 10";
    $result = $conn->query($sql);

    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($tags);
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Tag Input</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .tag-box {
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 1rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      min-height: 60px;
    }

    .tag {
      background-color: #0d6efd;
      color: white;
      padding: 0.3rem 0.75rem;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      font-size: 0.9rem;
    }

    .remove-tag {
      margin-left: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .autocomplete-suggestions {
      position: absolute;
      z-index: 1000;
      background-color: white;
      border: 1px solid #dee2e6;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
      border-radius: 0.375rem;
    }

    .autocomplete-suggestions div {
      padding: 0.5rem;
      cursor: pointer;
    }

    .autocomplete-suggestions div:hover {
      background-color: #f1f3f5;
    }
  </style>
</head>
<body>
<div class="container py-5" style="max-width: 600px;">
  <h4 class="mb-3">ค้นหา/เพิ่มแท็ก</h4>

  <div class="position-relative">
    <input type="text" id="tagInput" class="form-control" placeholder="พิมพ์เพื่อค้นหาแท็ก...">
    <div class="autocomplete-suggestions" id="suggestionBox" hidden></div>
  </div>

  <div class="tag-box mt-3" id="tagsDisplay"></div>
</div>

<script>
  const tagInput = document.getElementById('tagInput');
  const suggestionBox = document.getElementById('suggestionBox');
  const tagsDisplay = document.getElementById('tagsDisplay');
  const tags = [];

  tagInput.addEventListener('input', async () => {
    const query = tagInput.value.trim();
    if (query === '') {
      suggestionBox.hidden = true;
      return;
    }

    const res = await fetch(`?q=${encodeURIComponent(query)}`);
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
    }
  }
</script>
</body>
</html>
