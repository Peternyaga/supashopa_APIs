<?php
require_once "../includes/dbOperations.php";
$db=new dbOperations("db_supashopa");
$result=$db->searchForShop($_POST['query']);
echo json_encode($result);
?>