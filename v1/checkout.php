<?php
//session_start();

if (isset($_POST['userId']) && isset($_POST['grand_total']) && isset($_POST['items']) && isset($_POST['shop']) && isset($_POST['shop_id']) && isset($_POST['phone_number'])) {
    $userId = $_POST['userId'];
    //$phone="0759265709";
    $phone = $_POST['phone_number'];
    $items = json_decode($_POST['items'], true);
    $grandTotal = $_POST['grand_total'];
    $subtotal = $_POST['subtotal'];
    $serviceCost = $_POST['service_cost'];
    $shop_db_name = $_POST['shop'];
    $shopId = $_POST['shop_id'];

    require_once '../includes/dbConnect.php';

    $db = new dbConnect("db_shops");
    $con = $db->connect();
    $stmt = $con->prepare("SELECT * FROM `tbl_mpesa_integration` WHERE`shop id`=?");
    $stmt->bind_param("i", $shopId);

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = $result->fetch_assoc();
            //$businessShortCode = $response['BusinessShortCode'];
            $businessShortCode = "6789398";
            $passkey = $response['passkey'];
            //$consumerkey = $response['ConsumerKey'];
            $consumerkey = getenv('MPESA_CONSUMER_KEY');
            $consumerSecret = getenv('MPESA_CONSUMER_SECRET');
            $transactionType = $response['TransactionType'];
            $accountReference = $response['AccountReference'];

            $config = array(
                //'env' => 'sandbox',
                'env' => 'live',
                //'BusinessShortCode' => $businessShortCode,
                'BusinessShortCode' => "6789398",
                //'passkey' => $passkey,
                'passkey' => getenv('MPESA_PASSKEY'),
                'key' => $consumerkey,
                'secret' => $consumerSecret,
                'username' => 'apitest',
                'TransactionType' => $transactionType,
                'CallBackURL' => "https://probable-weasel-newly.ngrok-free.app/Daraja_callback.php",
                //'AccountReference' => $accountReference,
                'AccountReference' => "Naivas",
                'TransactionDesc' => 'test'
            );


            $phone = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
            $phone = (substr($phone, 0, 1) == '0') ? preg_replace("/^0/", "254", $phone) : $phone;
            $phone = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;

            $access_token = ($config['env'] == "live") ? "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials" :
                "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
            $credentials = base64_encode($config['key'] . ":" . $config['secret']);
            $ch = curl_init($access_token);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response);
            $token = isset($result->{'access_token'}) ? $result->{'access_token'} : "N/A";
            $password = "";

            if ($token != "N/A") {
                $timestamp = date("YmdHis");
                $password = base64_encode($config['BusinessShortCode'] . "" . $config['passkey'] . "" . $timestamp);
                $curl_post_data = array(
                    //'BusinessShortCode' => $config['BusinessShortCode'],
                    'BusinessShortCode' => $config['BusinessShortCode'],
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    //'TransactionType' => $config['TransactionType'],
                    'TransactionType' => "CustomerBuyGoodsOnline",
                    //'Amount'=>$grandTotal,
                    'Amount' => 1,
                    'PartyA' => $phone,
                    'PartyB' => $config['BusinessShortCode'],
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $config['CallBackURL'],
                    'AccountReference' => $config['AccountReference'],
                    'TransactionDesc' => $config['TransactionDesc']
                );

                $data_string = json_encode($curl_post_data);
                $endpoint = ($config['env'] == "live") ? "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest" : "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
                $ch = curl_init($endpoint);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);
                $result = json_decode(json_encode(json_decode($result)), true);
                if (array_key_exists("ResponseCode", $result)) {
                    if ($result["ResponseCode"] == "0") {
                        $merchantRequestId = $result["MerchantRequestID"];
                        $response = array(
                            'responsecode' => $result["ResponseCode"],
                            'message' => $result["CustomerMessage"],
                            'sessionid' => $result["MerchantRequestID"]
                        );
                        /*after mpesa responds, if the request has been accepted for processing, a new session is created and it is assigned an id to match the value of CheckuotRequestId in the response.
                        The new session id will be used in the callback API to access data saved in this session since the mpesa call back payload will contain the same CheckOutRequestId */
                        session_id($merchantRequestId);
                        session_start();
                        $_SESSION["access token"] = $token;
                        $_SESSION["userId"] = $userId;
                        $_SESSION["phone number"] = $phone;
                        $_SESSION["grand total"] = $grandTotal;
                        $_SESSION["shopDbName"] = $shop_db_name;
                        $_SESSION["shopid"] = $shopId;
                        $_SESSION["items"] = $items;
                        require_once '../includes/dbOperations.php';
                        $db = new dbOperations("db_shops");
                        $orderId = $db->placeOrder($shopId, $userId, $grandTotal, $merchantRequestId, 0, $subtotal, $serviceCost);
                        //require "../v1/business_buy_goods.php";
                        if (!!$orderId) {
                            for ($i = 0; $i < count($items); $i++) {
                                $item = ($items[$i]);
                                $itemId = $item["item id"];
                                $quantity = $item["quantity"];
                                $dbResponse = $db->orderItems($shopId, $orderId['id'], $itemId, $quantity);
                            }
                        } else {
                            $response = array();
                            $response['code'] = 2;
                            $response["error"] = true;
                            $response["message"] = "Order could not be added to database";
                            return json_encode($response);
                        }

                    } else {
                        $response = array(
                            'responsecode' => 6,
                            'message' => $result["errorMessage"]
                        );
                    }
                } else {
                    $response = array(
                        'responsecode' => 5,
                        'message' => $result["errorMessage"]
                    );
                }

            } else {
                $response = array(
                    'responsecode' => 4,
                    'message' => 'could not get access token'
                );
            }

        } else {
            $response = array('responsecode' => 3,
                'message' => 'mpesa integration details not set!');
        }
    } else {
        $response = array('responsecode' => 2,
            'message' => 'mpesa integration table is missing');
    }

} else {
    $response = array('responsecode' => 1,
        'message' => 'missing parameters');
}
echo json_encode($response);
?>
