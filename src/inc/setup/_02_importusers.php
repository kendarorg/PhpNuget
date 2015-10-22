<?php
if(!defined('__INSETUP__')){
	die("Error");
}

$useMySql = false;
?>
<html>
	<head>
	</head>
	<body>
	<!--	<h4>Import Users</h4>
		To import users from a previous version.
		<form method="POST" action="setup.php" enctype="multipart/form-data">
			<input type="hidden" id="dosetup" name="dosetup" value="importPackages"/>
			<table border=0>
				<tr><td>Users txt db:</td><td><input type="file" id="txtDb" name="txtDb" /></td></tr>
			</table>
			<input type="submit" value="Update!"></input>
		</form>
		<br>
		<h4>Finish</h4>
		When no more actions are required
		<form method="POST" action="setup.php">
			<input type="hidden" id="dosetup" name="dosetup" value="finishSetup"/>
			<input type="submit" value="Finished!"></input>
		</form>-->
		<ul>
<?php
	require_once(__ROOT__."/inc/internalsettings.php");
	//Create environment
	$r = array();
	
	$r["@MySqlServer@"] = UrlUtils::GetRequestParamOrDefault("mySqlServer","127.0.0.1","post");
	$r["@MySqlLogin@"] = UrlUtils::GetRequestParamOrDefault("mySqlLogin","phpnuget","post");
	$r["@MySqlPassword@"] = UrlUtils::GetRequestParamOrDefault("mySqlPassword","password","post");
	$r["@MySqlDb@"] = UrlUtils::GetRequestParamOrDefault("mySqlDb","phpnuget","post");
	
	define('__MYSQL_SERVER__',$r["@MySqlServer@"]);
	define('__MYSQL_USER__',$r["@MySqlLogin@"]);
	define('__MYSQL_PASSWORD__',$r["@MySqlPassword@"]);
	define('__MYSQL_DB__',$r["@MySqlDb@"]);

	if (isset($_POST['useMySql'])) {
		$useMySql = true;
		$r["@DbType@"] = "DBMYSQL";
		define('__DB_TYPE__',DBMYSQL);
	} else {
		$r["@DbType@"] = "DBTXT";
		define('__DB_TYPE__',DBTXT);
	}
	if($useMySql){
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		$result = mysqli_query( $connection,"CREATE TABLE IF NOT EXISTS Versions (VersionNumber CHAR(254),Id TINYINT,PRIMARY_KEY(Id))");
		$result = mysqli_query( $connection,"SELECT VersionNumber FROM Versions");
		$data = array();
		if($result){
			$data = mysqli_fetch_array($result,MYSQLI_ASSOC);
		}
		if(sizeof($data)==0){
			mysqli_query( $connection,"INSERT INTO Versions (VersionNumber,Id) VALUES ('".__DB_VERSION__."',0)");
		}else{
			while($data[0]["VersionNumber"]!=__DB_VERSION__){
				$result = mysqli_query( $connection,"SELECT VersionNumber FROM Versions");
				$data = mysqli_fetch_array($result,MYSQLI_ASSOC);
				require_once("updatemysql".$data[0]["VersionNumber"].".php");
			}
		}
			
		mysqli_close($connection);
	}else{
		if(file_exists(__ROOT__."/settings.php")){
			
			require_once(__ROOT__."/settings.php");
			
			require_once(__ROOT__."/inc/commons/smalltxtdb.php");
			$dbFile = "nugetdb_pkg.txt";
			$prever = stdVerifyVersion($dbFile);
			while($prever!=__DB_VERSION__){
				require_once("updatetxt.".stdVerifyVersion($dbFile).".php");
				$prever = updateTo4000($dbFile);
			}
			$dbFile = "nugetdb_usrs.txt";
			$prever = stdVerifyVersion($dbFile);
			while($prever !=__DB_VERSION__){
				require_once("updatetxt.".stdVerifyVersion($dbFile).".php");
				$prever = updateTo4000($dbFile);
				//echo stdVerifyVersion($dbFile);
				//break;
			}
		}
		
		
		
		
	}
	
	
	$r["@AdminUserId@"] = UrlUtils::GetRequestParamOrDefault("login","admin","post");
	$r["@AdminPassword@"] = UrlUtils::GetRequestParamOrDefault("password","password","post");
	$r["@AdminEmail@"] = UrlUtils::GetRequestParamOrDefault("email","nuget@".$_SERVER["SERVER_NAME"],"post");
	
	
	if (isset($_POST['packageUpdate'])) {
		$r["@PackageUpdate@"] = "true";
	} else {
		$r["@PackageUpdate@"] = "false";
	}
	$app =trim(UrlUtils::GetRequestParamOrDefault("applicationPath",$applicationPath,"post"),"/");
	if($app==""){
		$app="/";
	}else{
		$app="/".$app."/";
	}
	$r["@ApplicationPath@"] = $app;
	
	//Setup the settings
	Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/settings.php.template"),$r,Path::Combine(__ROOT__,"settings.php"));
	echo "<li>Settings initialized.</li>";
	if($r["@PackageUpdate@"] == "true"){
		echo "<li>Package update allowed (warning!).</li>";
	}else{
		echo "<li>Package update not allowed.</li>";
	}
	
	
	//Setup the htaccess for api v2 and v1
	$r["@HtAccess.V1@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/htaccess.v1"),$r);
	$r["@HtAccess.V2@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/htaccess.v2"),$r);
	$r["@HtAccess.V3@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/htaccess.v3"),$r);
	
	//Write the root htacces
	Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/htaccess.root",$r),$r,Path::Combine(__ROOT__,".htaccess"));
	echo "<li>Htaccess initialized with path '".$r["@ApplicationPath@"]."'.</li>";
	
	//Setup the web.config for api v2 and v1
	$r["@WebConfig.PHPEXE@"]=UrlUtils::GetRequestParamOrDefault("phpCgi","","post");
	$r["@WebConfig.V1@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/webconfig.v1"),$r);
	$r["@WebConfig.V2@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/webconfig.v2"),$r);
	$r["@WebConfig.V3@"] = Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/webconfig.v3"),$r);
	
	
	//Write the root web.config
	if($r["@WebConfig.PHPEXE@"]!=""){
		Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/webconfig.root",$r),$r,Path::Combine(__ROOT__,"web.config"));
	}else{
		Utils::ReplaceInFile(Path::Combine(__ROOT__,"inc/setup/webconfignophpexe.root",$r),$r,Path::Combine(__ROOT__,"web.config"));
	}
	echo "<li>Web.config initialized with path '".$r["@ApplicationPath@"]."'.</li>";
	
	
	require_once(__ROOT__."/settings.php");
	require_once(__ROOT__."/inc/db_users.php");
	$usersDb = new UserDb();
	//Create user
	$userEntity = new UserEntity();
	$userEntity->UserId = $r["@AdminUserId@"];
	$userEntity->Md5Password = md5($r["@AdminPassword@"]);
	$userEntity->Enabled = "true";
	$userEntity->Admin = "true";
	$userEntity->Name = "Administrator";
	$userEntity->Company = "";
	$userEntity->Email = $r["@AdminEmail@"];
	$usersDb->AddRow($userEntity,true);
	
	
	
	
	echo "<li>Admin User '".$userEntity->UserId."' added.</li>";
	?>
		</ul>
	</body>
</html>