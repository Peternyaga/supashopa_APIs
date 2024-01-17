<?php
require_once '../includes/dbOperations.php';
if (isset($_POST['item_id']) && isset($_POST['status'])){
    $itemId = $_POST['item_id'];
    $status = $_POST['status'];
    $db = new DBOperations("db_shops");
    //echo json_encode($_POST);
    echo $db->changeItemStatus($status, $itemId);
}else{
    echo "missing parameters";
}