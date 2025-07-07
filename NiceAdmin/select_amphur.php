<?php

include "connect_db.php";



  if (isset($_POST['function']) && $_POST['function'] == 'provinces') {
  	$id = $_POST['id'];
  	$sql = "SELECT * FROM amphur WHERE provinceID='$id'";
  	$query = mysqli_query($conn, $sql);
  	echo '<option value="" selected disabled>-กรุณาเลือกอำเภอ-</option>';
  	foreach ($query as $value) {
  		echo '<option value="'.$value['amphurID'].'">'.$value['amphurName'].'</option>';
  		
  	}
  }
?>