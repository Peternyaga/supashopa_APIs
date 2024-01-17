<?php
require_once "../includes/dbOperations.php";
if (isset($_POST['shop_id']) && isset($_POST['status'])){
    $shopId = $_POST['shop_id'];
    $status = $_POST['status'];
    $db = new DBOperations("db_supashopa");
   echo $db->changeShopStatus($status,$shopId);
}else{
    echo "some fields are missing";
}
