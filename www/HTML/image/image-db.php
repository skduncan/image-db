<?php
//mydb.php
// Sam Duncan

//session
session_set_cookie_params(3600);
session_start();

require "../phpStuff/classfun.php";
require "../phpStuff/mydb.php";
printDocHeadingJS("../styleSheets/image-style.css","Image Center","../phpStuff/image-db.js", "../phpStuff/jquery.js");
print "<body>\n<div class = 'content'>\n";
print "<h2>Image Database</h2>\n";
//printAll();

$valid = $_SESSION['loginValid'];
if($valid != 1)
{
   if(empty($_POST) || $_POST['tryAgain'] )
   {
	showLoginForm();
   }
   else
   {
	validateUser();
   }
}// end if NOT logged in

$valid = $_SESSION['loginValid'];  // reget in case login wored
if($valid == 1)
{
	if($_POST['submitFile'])
	{
		processNewImage();
		showLogoutForm();
		showUploadForm();
		showAllImages();
	}
	else if ($_POST['logOut'])
        {
	     doLogout();
        }
	else if ($_POST['removeSubmit'])
	{
		removeImage();
		showLogoutForm();
		showUploadForm();
		showAllImages();
	}
	else
	{
		showLogoutForm();
		showUploadForm();
		showAllImages();
	}
}


print "</div>\n";
printDocFooter();

//function that displays the login form to the user
function showLoginForm()
{
	$self = $_SERVER['PHP_SELF'];
	print "<div><form method = 'post' action = '$self'>\n";
	print "<h3>Please login to access the image library. </h3>\n";
	print "<h3>Please enter username: </h3>\n";
	print "<h3><input type = 'text' name = 'theUser' size = '10'".
	      " value = '$aUser'></h3>\n";
	print "<h3>Please enter password:</h3>\n";
	print "<h3><input type = 'password' name = 'thePassword' size = '10'".
	      " value = '$aPassword'></h3>\n";
	print "<input type = 'submit' name = 'loginSubmit' value = 'Login'>\n";
	print "</form>\n</div>\n";
}

//function that validates the user login
function validateUser()
{
	$self = $_SERVER['PHP_SELF'];
	$user = htmlentities($_POST['theUser'], ENT_QUOTES);
	$pwd = htmlentities($_POST['thePassword'], ENT_QUOTES);
	if($user == 'lbaker' && $pwd == 'cosc2328')
	{
		$_SESSION['loginValid'] = 1;
	}
	else
	{
		$_SESSION['loginValid'] = 0;
		print "Incorrect username and password given.";
		showLoginForm();
	}
}

//function that displays the upload image form to the user
function showUploadForm()
{
	$self = $_SERVER['PHP_SELF'];
	print "<h2>Choose image to upload: </h2>\n";
	print "<form method = 'post' action = '$self'".
	      "enctype = 'multipart/form-data'>\n";
	print "<input type = 'file' name = 'myFile' />\n<br />";
	print "<select name = 'category'>\n";
	      print "<option value = 'none'> Choose category from list </option>\n";
	      print "<option value = 'funny'> Funny </option>\n";
	      print "<option value = 'cool'> Cool </option>\n";
	      print "<option value = 'serious'> Serious </option>\n";
	print "</select>\n";
	print "<h4>Please enter a description for the image.</h4>\n";
	print "<input type = 'text' name = 'description' >\n<br />";
	print "<input type = 'submit' name = 'submitFile' value = 'upload' > \n";
	print "</form>\n";
}

//function that displays all images that exist within the database
//in the form of thumbnails, which when clicked, will display the
// full size image via JavaScript.
function showAllImages()
{
	$self = $_SERVER['PHP_SELF'];
	print "<h3>Current Images: </h3>\n";
	print "<div class = 'content'>\n";
	print "<h3> Full sized image will display here: </h3>\n";
	print "<h3> Please click one of the thumb images below </h3>\n";
	print "<div id = 'imageDiv'></div>\n";
	print "</div>\n";
	$db = dbConnect();
	//$db -> debug=true;
	$query = "Select imageId, description, category from myImages;";
	$result = $db -> Execute($query);
	if(!$result)
	{
		print "Error with displaying images <br />\n";
		return;
	}
	$count = $result -> rowCount();
	if($count == 0)
	{
		print "No images to display";
	}
	else
	{
		while($row = $result -> FetchRow())
		{
			$id = $row['imageId'];
			print "<div class = 'content'>\n";
			print "<div class = 'floatleft'>\n".
			      "Category = ".$row['category']."<br /><br />\n".
			      "Description = ".$row['description']."<br /><br />\n";
			      print "<div><form method = 'post' action = '$self'>\n";
			      print "<h6><input type = 'submit' name = 'removeSubmit' value = 'remove'>\n";
			      print "<h6><input type = 'hidden' name = 'removeId' value = '$id'></h6>\n";
			      print "</form>\n</div>\n";
			$thumb = "<img src ='imageviewthumb.php?id=".
                	$row['imageId']."&view=thumb' alt = 'myImage'/>";
			$full = "imageviewthumb.php?id=".$row['imageId']."&view=full";
			print "<a onclick = 'showPic($id);'> $thumb</a><br />\n";
			print "</div></div>";
		}
	}
}

//function that creates a thumbnail out of a image file
function createThumb($fullimagePath, $thumbimagePath)
{
	// load image and get image size
 	 $img = imagecreatefromjpeg($fullimagePath);
	 $width = imagesx( $img );
 	 $height = imagesy( $img );

	// calculate thumbnail size
	$new_width = 80;
	$new_height = floor( $height * ( $new_width / $width ) );

	// create a new temporary image
	$tmp_img = imagecreatetruecolor( $new_width, $new_height );
	// copy and resize old image into new image
 	imagecopyresized( $tmp_img, $img, 0, 0, 0, 0,
		    $new_width, $new_height, $width, $height );
		    // save thumbnail into a file

	imagejpeg($tmp_img, $thumbimagePath);
}

//function that processes the uploaded image
function processNewImage()
{
	$fullimagePath = $_FILES['myFile']['tmp_name'];
	//print_r ($_FILES);
	$thumbimagePath = "./thumb/thumb.jpg";
	$category = htmlentities($_POST['category'], ENT_QUOTES);
	$description = htmlentities($_POST['description'], ENT_QUOTES);

	if($category == 'none' && $description == "")
	{
		print "Incorrect category and/or no description entered.";
		return;
	}

	$info = getimagesize($fullimagePath);
	$ty = $info[2]; // type
	if($ty !=2)
	{

$error = "incorrect file type given. Please upload a jpg image.";
		print $error;
		return;

	}
	else
	{
		createThumb($fullimagePath, $thumbimagePath);
		$fileContents = addslashes(file_get_contents($fullimagePath));
		$thumbContents = addslashes(file_get_contents($thumbimagePath));
		$db = dbConnect();
		if(!$db)
		{
			print "Error in connecting to database.";
			return;
		}
		else
		{
			$query = "insert into myImages (fullImage, category, description, thumbImage)".
			       " values ('$fileContents','$category','$description','$thumbContents');";
			$result = $db -> Execute($query);
			if(!$result)
			{
				$queryError = "error in the query";
				print $queryError;
				return;
			}

		}
	}
}

//function that displays logout button
function showLogoutForm()
{
	 $self = $_SERVER['PHP_SELF'];
	 print "<div><form method = 'post' action = '$self'>\n";
	 print "<input type = 'submit' name='logOut' value = 'Log Out' />\n";
	 print "</form>\n</div>\n";
}

// handles the logging out problem
//post hidden variable loggedOut
function doLogout()
{
	$self = $_SERVER['PHP_SELF'];
	$_SESSION['loginValid'] = 0;
	startOverLink();
}

//Function that removes the image from the database.
function removeImage()
{
	$idRemove = htmlentities($_POST['removeId'], ENT_QUOTES);
	$db = dbConnect();
	//$db -> debug=true;
	$query = "DELETE FROM myImages WHERE imageId = '$idRemove';";
	$result = $db -> Execute($query);
}

//no blank lines after
?>
