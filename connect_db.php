
<?php
//mysqli("server name","username","password","database name")
$conn = new mysqli("127.0.0.1", "root","","booking_massage");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

?>