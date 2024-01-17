<?php
include_once "../includes/dbOperations.php";
$file = "C:\Users\peterngari/result.txt";
$payLoad = file_get_contents('php://input');//Receives the JSON result from Daraja
file_put_contents($file, $payLoad, FILE_APPEND);
file_put_contents($file, "\n", 8);

$content = json_decode($payLoad, true);
$db = new DBOperations("db_shops");
$conversationId = $content['Result']['ConversationID'];
session_id($db->getSessionId($conversationId));
session_start();
$resultCode = $content['Result']['ResultCode'];
$transactionID = $content['Result']['TransactionID'];
if ($content['Result']['ResultCode'] == 0){
echo "*********** merchant payment update". $db->updatePayoutDetails($resultCode, $transactionID, $conversationId)."********\n";
}
echo $content['Result']['ResultDesc'];