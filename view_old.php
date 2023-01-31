<link href="assets/css/bootstrap.css" rel="stylesheet" />
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.12.4.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-fileupload.js"></script>
<?php
include("functions.php");
$dblink=db_connect("docstorage");
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">View Files on DB</h1>';
echo '<div class="panel-body">';
$sql="Select * from `documents` where `status`='active'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	if ($data['path']!=NULL)
		echo '<p>File: <a href="uploads/'.$data['name'].'">'.$data['name'].'</a></p>';
	else
	{
		$content=$data['content'];
		$fname=date("Y-m-d_H:i:s")."-userid-file.pdf";
		if (!($fp=fopen("/var/www/html/uploads/$fname","w")))
			echo "<p>File could not be loaded at this time</p>";
		else
		{
			fwrite($fp,$content);
			fclose($fp);
			echo '<p>File: <a href="uploads/'.$fname.'">'.$data['name'].'</a></p>';
		}
	}
}
echo '</div>';//end panel-body
echo '</div>';//end page-inner
?>