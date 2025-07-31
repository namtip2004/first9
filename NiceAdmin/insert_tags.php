<?php
include("connect_db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tag_name = mysqli_real_escape_string($conn, $_POST['floatingtags']);

  // Insert tag
  $insert_tag = "INSERT INTO tag (tag_name) VALUES ('$tag_name')";
  if (mysqli_query($conn, $insert_tag)) {
    $tag_id = mysqli_insert_id($conn); // Get inserted tag ID

    // Insert selected services
    if (!empty($_POST['services'])) {
      foreach ($_POST['services'] as $service_id) {
        $service_id = intval($service_id);
        $insert_relation = "INSERT INTO tag_service (tag_id, service_id) VALUES ('$tag_id', '$service_id')";
        mysqli_query($conn, $insert_relation);
      }
    }

    echo "<script>alert('Tag and services added successfully'); window.location.href='tags_form.php';</script>";
  } else {
    echo "<script>alert('Error inserting tag'); window.location.href='table_tags.php';</script>";
  }
}
?>
