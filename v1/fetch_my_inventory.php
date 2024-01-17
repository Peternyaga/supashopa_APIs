<?php
$response=array();
require_once '../includes/dbOperations.php';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['shop_id'])) {
        $db=new dbOperations("db_shops");
        $response=$db->getAllItemsfromDb($_POST['shop_id']);
    }else{
        $response['error']=true;
        $response['message']='Missing or incorrect parameter';
    }
}else{
    $response['error']=true;
    $response['message']='use a "GET" Request method';
}
echo json_encode($response);