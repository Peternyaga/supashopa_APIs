<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    return 0;
}

require_once "../includes/dbOperations.php";

$payLoad = file_get_contents('php://input');//Receives the JSON result from Daraja

$content = json_decode($payLoad, true);

session_id($content['Body']['stkCallback']['MerchantRequestID']);
session_start();
$resultCode = $content['Body']['stkCallback']['ResultCode'];
$marchantRequestId = $content['Body']['stkCallback']['MerchantRequestID'];
$userId = $_SESSION["userId"];
$phone = $_SESSION["phone number"];
$amount = $_SESSION["grand total"];
$shop_db_name = $_SESSION["shopDbName"];
$shopId = $_SESSION["shopid"];
//$items=json_decode($_SESSION["items"], true);
$mpesaReceiptNumber = "";

if ($resultCode == 0) {
    $resultDesc = $content['Body']['stkCallback']['ResultDesc']; //gets result description from the callback payload
    $CallbackMetadata = $content['Body']['stkCallback']['CallbackMetadata']['Item'];
    for ($i = 0; $i < sizeof($CallbackMetadata); $i++) {
        $item = ($CallbackMetadata[$i]);
        if ($item["Name"] == "MpesaReceiptNumber") {
            $mpesaReceiptNumber = $item["Value"];
            $i = sizeof($CallbackMetadata);
        }
    }
    $db = new dbOperations("db_shops");
    $db->updateOrder("'" . $marchantRequestId . "'", 1, "'" . $resultDesc . "'", "'" . $mpesaReceiptNumber . "'");
    require "../v1/business_buy_goods.php";
} else {
    $resultDesc = $content['Body']['stkCallback']['ResultDesc']; //gets result description from the callback payload
    $db = new dbOperations("db_shops");
    $db->updateOrder($marchantRequestId, 0, $resultDesc, null);
    session_destroy();
}
?>