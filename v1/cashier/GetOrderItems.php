<?php
//This script returns a string containing details of all the items in a specific order.
$response=array();
if (isset($_POST['order_id']) && isset($_POST['shop_name'])) {
 	require_once '../includes/cashierDbOperations.php';
 	$db=new cashierDbOperations($_POST['shop_name']);
 	$response=$db->fetchOrderItems($_POST['order_id']);
 } else{
 	$response['error']=true;
 	$response['message']="some fields are missing";
 }
 echo json_encode($response);