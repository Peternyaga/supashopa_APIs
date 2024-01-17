<?php
/**
* 
*/
class DbConnect
{
	private $db_name;
	private $con;
	function __construct($db)
	{
		$this->db_name=$db;
	}
	function connect(){
		include_once dirname(__FILE__).'/constants.php';
		$this->con=new mysqli(DB_HOST,DB_USER,DB_PASSWORD,$this->db_name);

		if(mysqli_connect_errno()){
			echo "failed to connect with database";
		}
		return $this->con;
	}
}