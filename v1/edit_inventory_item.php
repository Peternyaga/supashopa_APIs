<?php
require_once '../includes/dbOperations.php';
if (isset($_POST['shop_id']) && isset($_POST['item_id']) && isset($_POST['item_name']) && isset($_POST['shop_name']) && isset($_POST['item_category']) && isset($_POST['item_price'])){
if (isset($_FILES['file'])){
    $shopId = $_POST['shop_id'];
    $itemId = $_POST['item_id'];
    $itemName = $_POST['item_name'];
    $shopName = $_POST['shop_name'];
    $category = $_POST['item_category'];
    $targetFile = $_FILES["file"];
    $price = $_POST['item_price'];
    $imageFileType = strtolower(pathinfo($targetFile["name"], PATHINFO_EXTENSION));
    $targetDir = "../../catalogue_images/".$shopName."/";
    $imageFileName = $_POST["item_name"] . "." . $imageFileType;
    $uploadResult = (move_uploaded_file($_FILES["file"]["tmp_name"], $targetDir . $imageFileName));

    if ($uploadResult){
        $db=new dbOperations("db_shops");
        $result = $db->editInventoryItem($shopId, $itemId, $itemName, $imageFileName, $price, $category);
        if ($result){
            echo  "success";
        }
    }
}
}else{
    echo "required parameters missing";
}
