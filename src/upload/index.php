<?php


require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/db_users.php");
require_once(__ROOT__."/inc/nugetreader.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/uploadutils.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");


uplog("upload","==================================");
uplog("upload","request: ".$_SERVER['REQUEST_URI']);
uplogh("upload","Post",$_POST);
uplogh("upload","Get",$_GET);

$temp_file = tempnam(sys_get_temp_dir(), 'Tux');
$result = array();
try{
	
	if (empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
		HttpUtils::ApiError('403', 'Invalid API key');
		uplog("upload","No api key!");
		die();
	}
	
	$token = strtoupper(trim(trim($_SERVER['HTTP_X_NUGET_APIKEY'],"{"),"}"));
	$db = new UserDb();
	$users = $db->Query("Token eq '{".$token."}'",1,0);

	if(sizeof($users)!=1){
		HttpUtils::ApiError('403', 'Invalid API key');
		uplog("upload","Wrong api key!");
		die();
	}
	
	uplog("upload","Validation done!");
	$user = $users[0];
	$uploader = new UploadUtils(Settings::$PackagesRoot,array("nupkg"),Settings::$MaxUploadBytes,true);
	$uploader->allowAll = true;
	uplog("upload","Upload utils initialized!");
	$result = $uploader->Upload("package");
	if($result['hasError']) { 
		uplogv("upload","UploadUtils error uploading",$result);
		throw new Exception($result['errorCode']); 
	}

	$fileName = basename($result["name"],".nupkg");

	$nugetReader = new NugetManager();
	uplog("upload","NugetManager initialized!");
	$parsedNuspec = $nugetReader->LoadNuspecFromFile($result["destination"]);
	uplogv("upload","Nuspec loaded!",$parsedNuspec);
	$parsedNuspec->UserId=$user->Id;
	$nuspecData = $nugetReader->SaveNuspec($result["destination"],$parsedNuspec);
	
	uplog("upload","Upload completed");
	// All done!
	header('HTTP/1.1 201 Created');
}catch(Exception $ex){
	uplogv("upload","Error uploading",$ex);
	if(array_key_exists ("destination",$result)){
		unlink($result["destination"]);
	}
	
	if($doUpLog){
		file_put_contents("upload.log",$ex->Message."\r\n", FILE_APPEND);
	}
	
	unlink($temp_file);
	HttpUtils::ApiError('500', $ex->getMessage());
	die();
}
?>