<?php
require_once '../includes/cashierDbOperations.php';
$response=array();
if (isset($_POST['shop_name'])&& isset($_POST['order_id'])&& isset($_POST['status'])) {
$db=new cashierDbOperations($_POST['shop_name']);
$db->UpdateOrderStatus($_POST['order_id'], $_POST['status']);
}else{
	$response['error']=true;
	$response['message']="missing shop name or order id";
	echo json_encode($response);
}