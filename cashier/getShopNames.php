<?php
/*Used to get shop names from the shops database.
shop names assist attendants during account activation.*/

require_once '../includes/shopsdbconnect.php';
$db=new shopsdbconnect;
$con=$db->connect();
$shop_names=array();
$stmt=$con->prepare("SELECT `shop name` FROM `tbl_shops`");
$stmt->execute(); 
$result=$stmt->get_result();
for ($i=0; $i < $result->num_rows; $i++) { 
	$shop_names[$i]=$result->fetch_assoc();
}
echo json_encode($shop_names);
