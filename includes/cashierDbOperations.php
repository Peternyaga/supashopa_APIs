<?php

class cashierDbOperations{
	private $response=array();
	private $con;


//To connect to the a shop database eg. the database of magunas supermarket, this constructor requires the name of that database. This constructor uses the name of the shop passed by the attendant to query for the name of the particular database from the database containing the records of all the shops.
	function __construct($shopName)
	{
		require_once dirname(__FILE__).'/dbConnect.php';
		$dbName=$this->getShopDbName($shopName);
		if (!$dbName==NULL) {
			$db=new dbConnect($dbName);
			$this->con=$db->connect();
		}else{
			$response['error']=true;
			$response['message']="The shop name you entered does not exist";
			return $response;
		}
		
		//$response=NULL;
	}

//function for getting the name of a shop database using the name of that particular shop
	function getShopDbName($shopName){
		$dbName=NULL;
		require_once dirname(__FILE__).'/shopsdbconnect.php';
		$shopsDb=new shopsdbconnect;
		$shopsDbcon=$shopsDb->connect();
		$stmt=$shopsDbcon->prepare("SELECT `database name` FROM `tbl_shops` WHERE `shop name`=?");
		$stmt->bind_param("s", $shopName);
		$stmt->execute();
		$result=$stmt->get_result();
		if ($result->num_rows>0) {
			$row=array();
			$row=$result->fetch_assoc();
			$dbName=$row['database name'];
		}
		return $dbName;
	}

	//function for activating the account of an attendant(To allow them to view orders). Each shop has a table (tbl_attendants) containing the records of potential attendants. For someone to activate their attendant account, their records must be saved in this table. This function checks whether a the details entered match any records in the table and whether any existing record has an active account. If the details match a record and the account is not activated, the account is activated.
	
	function activateAccount($name, $phone_no, $pass){
		$stmt=$this->con->prepare("SELECT * FROM `tbl_attendants` WHERE `Attendant_name`=?&&`Phone_ number`=?");
		$stmt->bind_param("ss", $name, $phone_no);
		$stmt->execute();
		$result=$stmt->get_result();
		$Password=md5($pass);

		if ($result->num_rows>0) {
			$response=$result->fetch_assoc();
			if ($response["Password"]!=NULL && $response["Account_status"]=="active") {
				$response=NULL;
				$response['error']=true;
				$response['message']='The account you are trying to activate is already active. Please go to login.';
			}elseif ($response["Password"]==NULL) {
				$response=NULL;
			$stmt2=$this->con->prepare("UPDATE `tbl_attendants` SET `Password`=?,`Account_status`=? WHERE `Attendant_name`=?&&`Phone_ number`=?");
			$account_status="active";
			$stmt2->bind_param("ssss",$Password, $account_status, $name, $phone_no);
			if ($stmt2->execute()) {
				$response['error']='false';
				$response['message']='your account has been activated.';
			}

		}
		}else{
			$response['error']='true';
			$response['message']='some of the details you entered are incorrect. please try again or see the system admin';
	}
			return $response;

	}

	public function logIn($phone, $password)
	{
		$stmt=$this->con->prepare("SELECT * FROM `tbl_attendants` WHERE `Phone_ number`=? &&`Password`=?");
		$stmt->bind_param("ss", $phone, $password);
		$stmt->execute();
		$result=$stmt->get_result();
		if ($result->num_rows>0) {
			$response['error']=false;
			$response['message']='login successful';
		}else{
			$response['error']=true;
			$response['message']='incorrect details';
		}
		return $response;
	}

	function fetchOrders(){
		require_once dirname(__FILE__).'/shopsdbconnect.php';
		$stmt=$this->con->prepare("SELECT * FROM `tbl_orders` WHERE `status`<2");

		//$stmt=$this->con->prepare("SELECT * FROM `tbl_orders` WHERE `status`<2 && `payment status`>0 &&`mpesa receipt id`!= 'NULL' ");
		$stmt->execute();
		$result=$stmt->get_result();
		$rowCount=0;
		$rows=array();
		$row=array();
		$name=array();

		while ($rowCount<$result->num_rows) {
			$row=$result->fetch_assoc();
			$user_id=$row["user_id"];
			$db=new shopsdbconnect();
			$supashopaCon=$db->connect();
			$stmt=$supashopaCon->prepare("SELECT `first name`,`last name` FROM `tbl_users` WHERE `user id`=$user_id");
			$stmt->execute();
			$nameResult=$stmt->get_result();
			$name=$nameResult->fetch_assoc();
			$row["name"]=$name["first name"]." ".$name["last name"];
			$rows[$rowCount]=$row;
			//$rows[$rowCount]=$result->fetch_assoc();*/
			$rowCount+=1;

		}
		return $rows;
	}

	/*This method returns an array containing details of all the items in a particular order. First, the method querys
	the item orders for the ids of items corresponding to the passed order id. The item ids returned  are then used to query the products table to get more details about the items with these product ids. */

	function fetchOrderItems($orderId){
		$orderItemId=array();
		$orderItems=array();
		$stmt=$this->con->prepare("SELECT `item_id`,`quantity` FROM `tbl_order_items` WHERE `order_id`=?");
		$stmt->bind_param('i', $orderId);
		$stmt->execute();
		$result=$stmt->get_result();
		$rowCount=0;
		while ($rowCount<$result->num_rows) {
			$orderItemId=$result->fetch_assoc();

			$stmt=$this->con->prepare("SELECT * FROM `tbl_products` WHERE `product_id`=?");
			$stmt->bind_param('s', $orderItemId['item_id']);
			$stmt->execute();
			$result2=$stmt->get_result();
			$item=$result2->fetch_assoc();
			$item['quantity']=$orderItemId['quantity'];
			$orderItems[$rowCount]=$item;
			$rowCount+=1;
		}

		return $orderItems;
	}

	public function UpdateOrderStatus($orderId,$status)
	{
		$stmt=$this->con->prepare("UPDATE `tbl_orders` SET `status`=? WHERE `id`=?");
		$stmt->bind_param('ii', $status, $orderId);
		$stmt->execute();
	}
}