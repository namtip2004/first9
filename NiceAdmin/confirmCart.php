<?php
    session_start();
    if(!isset($_SESSION['username'])){
        header("Location:index.html");
      }
    $member_id = $_SESSION['customer_id'];
    include "connect_db.php";
      date_default_timezone_set("Asia/Bangkok");
      $d = date("Y/m/d");
      $sql = "insert into orders(order_date,customer_id) 
              values('$d','$member_id')";
     echo "sql = ". $sql;
      mysqli_query($conn,$sql);
      

      $sql2 = "select order_id from orders order by order_id desc";
      echo "<br>sql2 = ".$sql2;
      $result = mysqli_query($conn,$sql2);
      $row = mysqli_fetch_assoc($result);
      $order_id = $row['order_id'];
      $total_price = 0;

    foreach ($_SESSION["cart_item"] as $item){
        //บันทึกการสั่งซื้อ
            $item_price = $item["quantity"]*$item["price"];
            $pid = $item["code"];
            $qty = $item["quantity"];
            $sql3 = "insert into order_items(order_id,product_id,quantity,sub_total) values('$order_id','$pid','$qty','$item_price')";
            mysqli_query($conn,$sql3);
            echo "<br>sql3 = ".$sql3;
            
            $total_price += $item_price;
        }
            $sql4 = "update orders set grand_total='$total_price' 
            where order_id='$order_id'";
            mysqli_query($conn,$sql4);
            echo "<br>sql4 = ".$sql4; 

            unset($_SESSION["cart_item"]);
    ?>

  
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Insert data to Members</title>
		<!-- sweet alert js & css -->
		<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">
	</head>

    <body>
        <script>
            setTimeout(function() {
               swal({
                    title: "บันทึกข้อมูลเรียบร้อย!", //ข้อความ เปลี่ยนได้ เช่น บันทึกข้อมูลสำเร็จ!!
                    text: "กลับหน้าหลัก", //ข้อความเปลี่ยนได้ตามการใช้งาน
                    type: "success", //success, warning, danger
                    timer: 3000, //ระยะเวลา redirect 3000 = 3 วิ เพิ่มลดได้
                    showConfirmButton: false //ปิดการแสดงปุ่มคอนเฟิร์ม ถ้าแก้เป็น true จะแสดงปุ่ม ok ให้คลิกเหมือนเดิม
                    }, function(){
                    window.location.href = "tables-data-orders.php"; //หน้าเพจที่เราต้องการให้ redirect ไป อาจใส่เป็นชื่อไฟล์ภายในโปรเจคเราก็ได้ครับ เช่น admin.php
                    });
            });
			
		</script>
	</body>
</html>

