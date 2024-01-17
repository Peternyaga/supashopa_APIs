<?php
require_once '../includes/dbConnect.php';
$db = new dbConnect("db_supashopa");
$con = $db->connect();
$categories = array();
$stmt = $con->prepare("SELECT * FROM `shop categories`");
$stmt->execute();
$result = $stmt->get_result();
for ($i = 0; $i < $result->num_rows; $i++) {
    $categories[$i] = $result->fetch_assoc();
}
echo json_encode($categories);