<?php
require_once '../includes/cashierDbOperations.php';
if (isset($_POST['shop_name'])) {
	$db=new cashierDbOperations($_POST['shop_name']);
	$orders=$db->fetchOrders();
	echo json_encode($orders);
}
