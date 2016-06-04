<?php


require('commons.php');
require('clsDB.php');
require('config.php');

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$sql = "SELECT u.* FROM users u
			;";

$results = $db->query($sql);
$rows = $results->rows;

$output = json_encode($rows);

echo $output;


?>

