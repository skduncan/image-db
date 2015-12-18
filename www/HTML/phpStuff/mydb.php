<?php
function dbConnect()
{
	$host = "*********";
	$user = "*********";
	$pwd = "**********";
	$database = "sduncan";
	include "../adodb5/adodb.inc.php";
	$db = newADOConnection('mysqli');
	$db -> Connect($host, $user, $pwd, $database);
	return $db;
}
?>
