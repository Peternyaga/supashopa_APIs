<?php
require_once '../includes/cashierDbOperations.php';
$response=array();

if (isset($_POST['shop_name']) && isset($_POST['phone']) && isset($_POST['password'])) {
	$password=md5($_POST['password']);
	$db=new cashierDbOperations($_POST['shop_name']);
	$response=$db->logIn($_POST['phone'], $password);
}
echo json_encode($response);