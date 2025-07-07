<?php
header('Content-Type: application/json');
include "connect_db.php";
$sqlQuery = "SELECT substr(pay_date,1,7) as yr,
                sum(grand_total) as sumPrice
                FROM orders
                where substr(pay_date,1,4) = '2024'
                and status=3
                GROUP by yr ";

$result = mysqli_query($conn,$sqlQuery);

$data = array();
foreach ($result as $row) {
    if(substr($row['yr'],5,2)=="01") $mn = "มค.";
    else if(substr($row['yr'],5,2)=="02") $mn = "กพ.";
    else if(substr($row['yr'],5,2)=="03") $mn = "มีค.";
    else if(substr($row['yr'],5,2)=="04") $mn = "เมย.";
    else if(substr($row['yr'],5,2)=="05") $mn = "พค";
    else if(substr($row['yr'],5,2)=="06") $mn = "มิย.";
    else if(substr($row['yr'],5,2)=="07") $mn = "กค.";
    else if(substr($row['yr'],5,2)=="08") $mn = "สค.";
    else if(substr($row['yr'],5,2)=="09") $mn = "กย.";
    else if(substr($row['yr'],5,2)=="10") $mn = "ตค.";
    else if(substr($row['yr'],5,2)=="11") $mn = "พย.";
    else if(substr($row['yr'],5,2)=="12") $mn = "ธค.";

    $point = array("label" => $mn , "y" => $row['sumPrice']);
    array_push($data,$point);

}

mysqli_close($conn);
echo json_encode($data, JSON_NUMERIC_CHECK);

?>