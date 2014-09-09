<?php
if(!defined('__INSETUP__')){
	die("Error");
}
?>
<html>
	<head>
	</head>
	<body>
		<h4>First initialization</h4>
		<!--This must be made first. To initialize all the data structures.<br>
		If you intend to import the users these values <u>must be corresponding to your old administrative account.</u><br><br>-->
		<form method="POST" action="setup.php">
			<input type="hidden" id="dosetup" name="dosetup" value="importUsers"/>
			<table border=0>
				<tr><td>Admin UserId:</td><td><input type="text" id="login" name="login" value="admin"/></td></tr>
				<tr><td>Admin Password:</td><td><input type="password" id="password" name="password" value="password"/></td></tr>
				<tr><td>Admin Email:</td><td><input type="text" id="email" name="email" value="nuget@<?php echo $_SERVER["SERVER_NAME"]; ?>"/></td></tr>
				<tr><td>Application Path:</td><td><input type="text" id="applicationPath" name="applicationPath" value="<?php echo $applicationPath;?>"/></td></tr>
				<tr><td>php-cgi.exe (for IIS):</td><td><input type="text" id="phpCgi" name="phpCgi" value="C:\Program Files (x86)\PHP\v5.3\php-cgi.exe"/></td></tr>
			</table>
			<input type="submit" value="Install!"></input>
		</form>
	</body>
</html>