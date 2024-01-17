<?php
require_once '../includes/dbOperations.php';
$response=array();
if ($_SERVER['REQUEST_METHOD']=='POST') {
	if (
		//isset($_POST['user_id']) and 
		isset($_POST['first_name']) and
		isset($_POST['email']) and 
		isset($_POST['last_name'])and
		//isset($_POST['residence'])and
		//isset($_POST['city_town'])and
		isset($_POST['phone'])and
		//isset($_POST['address'])and
		isset($_POST['password'])
		) {
			$db=new DBOperations('db_supashopa');
			$userExists= $db->userExists($_POST['phone'], $_POST['email']);
		if ($userExists!=null) {
			if ($userExists['phone number']==$_POST['phone']) {
				$response['error']=true;
				$response['message']="The phone number you entered has already registered an account";
			}elseif ($userExists['email']==$_POST['email']) {
				$response['error']=true;
				$response['message']="The email you entered has already registered an account";
			}
		}elseif($db->createUser($_POST['user_id'],$_POST['phone'],$_POST['first_name'],$_POST['last_name'],$_POST['address'],$_POST['city_town'],$_POST['residence'],$_POST['email'],$_POST['password'])){
				$response['error']=false;
				$response['message']="user registered successfully";
			}else{
				$response['error']=true;
				$response['message']="an error occurred, please try again";
			}

	}else{
		$response['error']=true;
		$response['message']="required field is missing";
	}
}else{
	$response['error']=true;
	$response['message']="invalid request";
}

echo json_encode($response);
?>

