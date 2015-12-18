<?php
require "../phpStuff/mydb.php";
$db = dbConnect();
$id = $_GET['id'];
$view = $_GET['view'];

if($view == 'full')
{
	$query = "select fullImage from myImages where imageId = '$id';";
}
else
{
	$query = "select thumbImage from myImages where imageId = '$id';";
}
$result = $db -> Execute($query);

if(!$query)
{
	print "Error: ".$db -> ErrorMsg()."<br />";
}

if($view == "full")
{
	$row = $result -> FetchRow();
	$bytes = $row['fullImage'];
}
else
{
	$row = $result -> FetchRow();
	$bytes = $row['thumbImage'];
}

header("Content-type:image/jpeg");
print $bytes;


?>
