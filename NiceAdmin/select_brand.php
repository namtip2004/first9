</script>
<?php

include "connectDB.php";


  if (isset($_POST['function']) && $_POST['function'] == 'product') {
  	$id = $_POST['id'];
  	$sql = "SELECT * FROM brand WHERE id='$id'";
  	$query = mysqli_query($conn, $sql);
  	echo '<option value="" selected disabled>-กรุณาเลือกเเบรนด์-</option>';
  	foreach ($query as $value) {
  		echo '<option value="'.$value['brand_id'].'">'.$value['brand_name'].'</option>';
  		
  	}
  }
?>