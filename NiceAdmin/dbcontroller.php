<?php
class DBController {

	//$conn = new mysqli("127.0.0.1", "root","","product_system","3310");
	private $host = "localhost";
	private $user = "root";
	private $password = "";
	//private $database = "blog_samples";
	private $database = "product_system";
	//private $port = "3310";
	private $conn;

	function __construct() {
		$conn = $this->connectDB();
		if(!empty($conn)) {
			$this->selectDB($conn);
		}
	}
	
	function connectDB() {
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database,/*$this->port*/);
		return $conn;
	}
	
	function selectDB($conn) {
		$this->conn = $this->connectDB();
	}
	
	function runQuery($query) {
		$result = mysqli_query($this->conn,$query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}		
		if(!empty($resultset))
			return $resultset;
	}
	
	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;		
	}
}
?>