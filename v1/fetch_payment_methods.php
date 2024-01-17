<?php
require_once '../includes/dbConnect.php';
$db = new dbConnect("db_supashopa");
$con = $db->connect();
$methods = array();
$stmt = $con->prepare("SELECT * FROM `payment methods`");
$stmt->execute();
$result = $stmt->get_result();
for ($i = 0; $i < $result->num_rows; $i++) {
    $methods[$i] = $result->fetch_assoc();
}
echo json_encode($methods);