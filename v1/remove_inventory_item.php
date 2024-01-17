<?php
include_once "../includes/dbOperations.php";
if(isset($_POST['shop_id']) && isset($_POST['item_id']) && isset($_POST['shop_name']) && isset($_POST['image_name'])){
    $db = new DBOperations("db_shops");
    $shopId = $_POST['shop_id'];
    $itemId = $_POST['item_id'];
    $shopName = $_POST['shop_name'];
    $image = $_POST['image_name'];
    $result = $db->removeInventoryItem($shopId, $itemId, $image, $shopName);
    echo $result;
}