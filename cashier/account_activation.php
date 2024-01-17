<?php
require_once '../includes/cashierDbOperations.php';
//$db=new cashierDbOperations($_POST['shop_name']);;
$response=array();

if ($_SERVER['REQUEST_METHOD']=='POST') {
	if (isset($_POST['shop_name'])&&isset($_POST['name'])&&isset($_POST['phone_no'])&&isset($_POST['password'])) {
		$db=new cashierDbOperations($_POST['shop_name']);
		//$response=$db->getShopDbName($_POST['shop_name']);
		//$response['shop name']=$_POST['shop_name'];
		$response=$db->activateAccount($_POST['name'], $_POST['phone_no'],$_POST['password']);
	}else{
		$response['error']='true';
		$response['message']='all the fields are required';
	}
}else{
	$response['error']='true';
	$response['message']='use a POST method';
}
echo json_encode($response);