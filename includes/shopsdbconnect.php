<?php

class shopsdbconnect{
private $con;
	function __construct()
	{
		# code...
	}

	function connect(){
$servername="localhost";
 $username= "root";
 $password= "";
 $databaseName= "db_supashopa";
 		$this->con=new mysqli($servername, $username, $password, $databaseName);

		if (mysqli_connect_errno()) {
			echo "failed to connect with db_shops";
		}
			//echo "connection to database was successful";
		

		return $this->con;
	}
	
}