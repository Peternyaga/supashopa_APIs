<?php
require_once '../includes/dbOperations.php';
$response=array();
if ($_SERVER['REQUEST_METHOD']=='POST') {
	if (
		isset($_POST['phone']) and
		isset($_POST['password'])) 
		{
			$db=new dbOperations('supashopa');
			if($db->login($_POST['phone'], $_POST['password'])){
				$response=$db->userExists($_POST['phone'], "");
				$response["error"]=false;
				$response["message"]="login successful";

			}else{
				$response["error"]=true;
				$response["message"]="wrong number or password";
			}
			
		}else{
			$response["error"]=true;
			$response["message"]="Required fields missing";
		}
		
}
echo json_encode($response);
//echo json_encode($userdetails);
		