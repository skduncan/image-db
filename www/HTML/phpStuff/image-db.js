//Javascript for assignment 8
// Sam Duncan
//cosc2328
// November 4th, 2014

function showPic(id)
{
      var output = "<img src = "+
	           "'http://www.cs.stedwards.edu/~sduncan/cosc2328/imageviewthumb.php?id="+id+
	           "&view=full' alt='full image'"+
	           " >\n";
      $("#imageDiv").html(output);
}
