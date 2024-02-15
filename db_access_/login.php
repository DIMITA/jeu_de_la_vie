<?php
header("Access-Control-Allow-Origin: *");
require_once('./dbh.php');

$db = DBH::getInstance();

$user = $_POST;


$userRec = $db->getUserByName($user["name"]);


if (password_verify($user["password"], $userRec['password'])) {
    header('Content-Type: application/json');
    echo json_encode($userRec);
} else {
    header('HTTP/1.1 401 Unauthorized', true, 401);
    echo "Failed";
}
