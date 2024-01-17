<?php
require_once "../includes/dbOperations.php";
$db=new dbOperations("db_shops");
$response=$db->checkOrderStatus($_POST['session_id']);
echo json_encode($response);
?>