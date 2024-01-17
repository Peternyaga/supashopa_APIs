<?php
require_once '../includes/dbOperations.php';
$result=array();
if ($_SERVER['REQUEST_METHOD']=='POST') {
	if (isset($_POST['query'])) {
		$db=new dbOperations("db_shops");
		$result=$db->searchItems($_POST['shop_id'], $_POST['query']);
	}else{
		$result['error']=true;
		$result['message']="nothing to search";
	}
}else{
	$result['error']=true;
	$result['message']="use a POST method!";
}
echo json_encode($result);
