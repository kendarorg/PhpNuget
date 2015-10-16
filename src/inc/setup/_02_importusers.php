<?php
if(!defined('__INSETUP__')){
	die("Error");
}
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
	//Create environment
	$r = array();
	
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