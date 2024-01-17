<?php
require_once '../includes/dbOperations.php';
$response = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//echo json_encode($_FILES['file']);
//echo json_encode($_POST);

    if (
        isset($_FILES['file']) and
        isset($_POST['shop_name']) and
        isset($_POST['shop_description']) and
        isset($_POST['category']) and
        isset($_POST['payment']) and
        isset($_POST['latitude']) and
        isset($_POST['longitude'])
    ) {
        $targetFile = $_FILES["file"];
        $imageFileType = strtolower(pathinfo($targetFile["name"], PATHINFO_EXTENSION));
        $targetDir = "../../shop images/";
        $imageFileName = $_POST["shop_name"] . "." . $imageFileType;
        $uploadResult = (move_uploaded_file($_FILES["file"]["tmp_name"], $targetDir . $imageFileName));
        if ($uploadResult) {
            $shopName = $_POST['shop_name'];
            $category = $_POST['category'];
            $method = $_POST['payment'];
            $description = $_POST['shop_description'];
            $latitude = $_POST['latitude'];
            $longitude = $_POST['longitude'];
            $mpesaTill = 'null';
            $pochiLaBiashara = 'null';
            $paybill = 'null';
            $account = 'null';
            if ($method == 1) {
                $mpesaTill = $_POST['mpesa_till'];
            } elseif ($method == 2) {
                $paybill = $_POST['mpesa_paybill'];
                $account = $_POST['paybill_account'];
            } elseif ($method == 3) {
                $pochiLaBiashara = $_POST['pochi_la_biashara'];
            }

            $paymentDetails = array(
                'method' => $method,
                'till' => $mpesaTill,
                'paybill' => $paybill,
                'account' => $account,
                'pochi' => $pochiLaBiashara
            );

            $db = new dbOperations('db_supashopa');
            $responseCode = $db->createShop($shopName, $category, $description, $latitude, $longitude, $imageFileName, $paymentDetails);
            $error = true;
            if ($responseCode === 101) {
                $message = "failed because payment details could not be added. please check your payment details again";
            } elseif ($responseCode === 100) {
                $message = "failed! your shop name might not be unique";
            }elseif ($responseCode){
                $message = "success";
                $error = false;
            }
            $response["code"] = $responseCode;
            $response["error"] = $error;
            $response["message"] = $message;
        }

    } else {
        $response["error"] = true;
        $response["message"] = "Required fields missing";
    }
} else {
    $response["error"] = true;
    $response["message"] = "use a POST method";
}
echo json_encode($response);
