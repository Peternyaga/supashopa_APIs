<?php

/**
* 
*/
class DBOperations
{
    private $con;

    function __construct($db_name)
    {
        //$this->db_name=$db_name;
        require_once dirname(__FILE__) . '/dbConnect.php';
        $db = new dbConnect($db_name);
        $this->con = $db->connect();
    }

    public function createUser($user_id, $phone, $first_name, $last_name, $address, $city_town, $residence, $email, $password)
    {
        $password = md5($password);
        $stmt = $this->con->prepare("INSERT INTO `tbl_users`(`user id`, `first name`, `last name`, `phone number`, `email`, `password`) VALUES (null,?,?,?,?,?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $phone, $email, $password);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login($phone, $pass)
    {
        $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM `tbl_users` WHERE `phone number`=? && `password`=?");
        $stmt->bind_param("ss", $phone, $password);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function userExists($phone, $email)
    {
        $stmt = $this->con->prepare("SELECT * FROM `tbl_users` WHERE `phone number`= ?||`email`= ?");
        $stmt->bind_param("ss", $phone, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();

    }

    public function getitemsfromdb($shopId)
    {
        $stmt = $this->con->prepare("SELECT * FROM `tbl_products` WHERE `shop code`=? ORDER BY `category` DESC");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowcount = 0;
        $rows = array();

        while ($rowcount < $result->num_rows) {
            $rows[$rowcount] = $result->fetch_assoc();
            $rowcount += 1;
        }

        return ($rows);

    }

    public function getAllItemsfromDb($shopId)
    {
        $stmt = $this->con->prepare("SELECT * FROM `tbl_products` WHERE `shop code`=? ORDER BY `category` DESC");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowcount = 0;
        $rows = array();

        while ($rowcount < $result->num_rows) {
            $rows[$rowcount] = $result->fetch_assoc();
            $rowcount += 1;
        }

        return ($rows);

    }

    public function searchItems($shopId, $query)
    {
        $stmt = $this->con->prepare('SELECT * FROM `tbl_products` WHERE `shop code` = ? AND `name` LIKE ? ORDER BY `category`');
        $stmt->bind_param("ss", $shopId, $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = array();
        $count = 0;
        while ($count < $result->num_rows) {
            $rows[$count] = $result->fetch_assoc();
            $count += 1;
            # code...
        }
        return ($rows);
    }

    public function placeOrder($shopId, $userId, $total, $sessionId, $paymentStatus, $subTotal, $serviceCost)
    {
        $query = "INSERT INTO `tbl_orders`(`shop id`, `user_id`, `session_id`, `total`, `sub total`, `service cost`, `status`, `payment status`, `payment result desc`, `mpesa receipt id`) VALUES 
                                        ($shopId,$userId,\"$sessionId\",$total,$subTotal,$serviceCost,0,$paymentStatus,'pending',null)";
        //echo $query;
        $result = mysqli_query($this->con, $query);
        if ($result == true) {
            $stmt2 = $this->con->prepare("SELECT `id` FROM `tbl_orders` WHERE `session_id`=?");
            $stmt2->bind_param("s", $sessionId);
            $stmt2->execute();
            $user = $stmt2->get_result();
            return $user->fetch_assoc();
        } else {
            return false;
        }

    }

    public function orderItems($shopId, $orderId, $itemId, $quantity)
    {
        $stmt = $this->con->prepare("INSERT INTO `tbl_order_items` (`shop id`, `order_id`, `item_id`, `quantity`) VALUES (?, ?, ?, ?)");
        if ($stmt != false) {
            $stmt->bind_param("siss", $shopId, $orderId, $itemId, $quantity);
            if ($stmt->execute()) {
                $response['code'] = 0;
                $response['error'] = false;
                $response['message'] = "your order has been received";
            } else {
                $response['code'] = 1;
                $response['error'] = true;
                $response['message'] = "failed! Please try again";
            }

        } else {
            $response['code'] = 2;
            $response['error'] = true;
            $response['message'] = "connection failed! Please try again";
        }
        return $response;
    }

    public function updateOrder($sessionId, $paymentStatus, $paymentResultDescription, $mpesaReceiptId)
    {
        $query = "UPDATE `tbl_orders` SET `payment status`=$paymentStatus,`payment result desc`=$paymentResultDescription,`mpesa receipt id`=$mpesaReceiptId WHERE `session_id`=$sessionId";
        return mysqli_query($this->con, $query);
    }

    public function checkOrderStatus($sessionId)
    {
        $stmt = $this->con->prepare("SELECT `payment status`FROM `tbl_orders` WHERE `session_id`=?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();

    }

    public function searchForShop($query)
    {
        $stmt = $this->con->prepare("SELECT * FROM `tbl_shops` WHERE `shop name` LIKE ?");
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            for ($i = 0; $i < $result->num_rows; $i++) {
                $response[$i] = $result->fetch_assoc();
            }
            return $response;
        }
        return 0;
    }


    public function createShop($shopName, $category, $shopDescription, $latitude, $longitude, $imageUrl, $paymentDetails)
    {
        $stmt = $this->con->prepare("INSERT INTO `tbl_shops`(`category`, `shop name`, `shop description`, `shop image URL`, `latitude`, `longitude`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssdd", $category, $shopName, $shopDescription, $imageUrl, $latitude, $longitude);
        if ($stmt->execute()) {
            $Name = "\"$shopName\"";
            $Description = "\"$shopDescription\"";
            $stmt = "SELECT `shop code`FROM `tbl_shops` WHERE `shop name`=$Name && `shop description` =$Description";
            $result = mysqli_query($this->con, $stmt);
            $row = mysqli_fetch_assoc($result);
            $shopId = $row['shop code'];
            $db = new dbConnect("db_shops");
            $tmpcon = $db->connect();
            $method = $paymentDetails['method'];
            $till = $paymentDetails['till'];
            $paybill = $paymentDetails['paybill'];
            $account = $paymentDetails['account'];
            $pochi = $paymentDetails['pochi'];
            $stmt = "INSERT INTO `payment_details`(`shop_id`, `payment method`, `mpesa till`, `mpesa paybill`, `account number`, `mpesa pochi number`) 
                        VALUES ($shopId,$method,$till,$paybill,$account,$pochi)";
            $result = mysqli_query($tmpcon, $stmt);
            $tmpcon->close();
            if (!$result) {
                $stmt = "DELETE FROM `tbl_shops` WHERE `shop code`=$shopId";
                if (mysqli_query($this->con, $stmt)) {
                    $this->con->close();
                    return 101;
                }
                return 102;
            }
            $shopItemsDir = "C:\\xampp\htdocs/catalogue_images/$shopName";
            if (!is_dir($shopItemsDir)) {
                $result = mkdir($shopItemsDir, 0777, true, null);
                return $result;
            } else {
                return 103;
            }
        } else {
            return 100;
        }
    }

    public function editInventoryItem($shopId, $itemId, $itemName, $itemImage, $price, $category)
    {
        $query = "UPDATE `tbl_products` SET `product_picture`=\"$itemImage\",`name`=\"$itemName\",`unit_price`=$price,`category`=\"$category\" WHERE `product_id`=$itemId && `shop code`=$shopId";
        return mysqli_query($this->con, $query);
    }

    public function changeItemStatus($status, $itemId)
    {
        $query = "UPDATE `tbl_products` SET `status`=$status WHERE `product_id`=$itemId";
        return mysqli_query($this->con, $query);
    }

    public function removeInventoryItem($shopId, $itemId, $itemImageName, $shopName)
    {
        $query = "DELETE FROM `tbl_products` WHERE `product_id`=$itemId&&`shop code`=$shopId";
        if (mysqli_query($this->con, $query)) {
            $imageFile = "../../catalogue_images/" . $shopName . "/" . $itemImageName;
            if (file_exists($imageFile)) {
                if (unlink($imageFile)) {
                    return 0;
                } else {
                    return 103;
                }
            } else {
                return 102;
            }
        } else {
            return 101;
        }
    }

    public function changeShopStatus($status, $shopCode)
    {
        $query = "SELECT `status` FROM `tbl_shops` WHERE `shop code`=$shopCode";
        $result = mysqli_query($this->con, $query);
        $result = mysqli_fetch_assoc($result);
        if ($result['status'] == 1) {
            echo "This shop is awaiting approval";
            return 101;
        } else {
            $query = "UPDATE `tbl_shops` SET `status`=$status WHERE `shop code` =$shopCode";
            return mysqli_query($this->con, $query);
        }
    }

    /*
     * function to update  merchant payout id. */
    public function setPayoutId($conversationId, $sessionId){
        $sql = "UPDATE `tbl_orders` SET `payout id` = \"$conversationId\" WHERE `session_id` = \"$sessionId\"";
        return mysqli_query($this->con, $sql);
    }

    /*
     * function to get session id using a payout id*/

    public function getSessionId($payoutId){
        $sql = "SELECT `session_id` FROM `tbl_orders` WHERE `payout id` = \"$payoutId\"";
        $result = mysqli_fetch_assoc(mysqli_query($this->con, $sql));
        echo $result['session_id'];
        return $result['session_id'];
    }

    /*
     * function to update payout result code and receipt id*/

    public function updatePayoutDetails($resultCode, $receiptId, $payoutId){
        $sql = "UPDATE `tbl_orders` SET `payout result code`= \"$resultCode\",`payout receipt id`=\"$receiptId\" WHERE `payout id`=\"$payoutId\"";
        return mysqli_query($this->con, $sql);
    }
}
