<?php
require_once '../includes/shopsdbconnect.php';
$db=new shopsdbconnect;
$con=$db->connect();
$shop=array();
$stmt=$con->prepare("SELECT * FROM `tbl_shops`");
$stmt->execute(); 
$result=$stmt->get_result();
for ($i=0; $i < $result->num_rows; $i++) { 
	$shop[$i]=$result->fetch_assoc();
}
echo json_encode($shop);