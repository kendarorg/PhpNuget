<?php
if(!defined('__INSETUP__')){
	die("Error");
}
$message = "";
$results = array();
$error = false;
if ($_FILES["file"]["error"] > 0) {
  $message = "Error: " . $_FILES["file"]["error"] . "<br>";
  $error = true;
} /*else {
  echo "Upload: " . $_FILES["file"]["name"] . "<br>";
  echo "Type: " . $_FILES["file"]["type"] . "<br>";
  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["file"]["tmp_name"];
}*/
?>
<html>
	<head>
	</head>
	<body>
		
		<ul>
			<?php if($error){ echo "<li>".$message."</li>";}else{
			foreach($results as $r){
			?>
			<li><?php echo $r; ?></li>
			<li>User 2 imported</li>
			<?php }} ?>
		</ul>
		<h4>Import Packages</h4>
		To import the packages db from a previous version. The files will be uploaded after.
		<form method="POST" action="setup.php">
			<input type="hidden" id="dosetup" name="dosetup" value="finishSetup"/>
			<!--<table border=0>
				<tr><td>Admin UserId:</td><td><input type="text" id="login" name="login" value="admin"/></td></tr>
				<tr><td>Admin Password:</td><td><input type="password" id="password" name="password" value="password"/></td></tr>
				<tr><td>Admin Email:</td><td><input type="text" id="email" name="email" value="nuget@<?php echo $_SERVER["SERVER_NAME"]; ?>"/></td></tr>
				<tr><td>Application Path:</td><td><input type="text" id="applicationPath" name="applicationPath" value="<?php echo $applicationPath;?>"/></td></tr>
			</table>-->
			<input type="submit" value="Update!"></input>
		</form>
		<br>
		<h4>Finish</h4>
		When no more actions are required
		<form method="POST" action="setup.php">
			<input type="hidden" id="dosetup" name="dosetup" value="finishSetup"/>
			<input type="submit" value="Finished!"></input>
		</form>
	</body>
</html>