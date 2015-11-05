<?php


require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/db_users.php");
require_once(__ROOT__."/inc/nugetreader.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/uploadutils.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");

$doUpLog = false;

if($doUpLog){
	file_put_contents("upload.log","==================================\r\n", FILE_APPEND);
	file_put_contents("upload.log","request: ".$_SERVER['REQUEST_URI']."\r\n", FILE_APPEND);
	if(sizeof($_POST)>0){
		file_put_contents("upload.log",var_export($_POST,true)."\r\n", FILE_APPEND);
	}
	if(sizeof($_GET)>0){
		file_put_contents("upload.log",var_export($_GET,true)."\r\n", FILE_APPEND);
	}
}

$temp_file = tempnam(sys_get_temp_dir(), 'Tux');
$result = array();
try{
	
	if (empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
		HttpUtils::ApiError('403', 'Invalid API key');
		die();
	}
	
	$token = strtoupper(trim(trim($_SERVER['HTTP_X_NUGET_APIKEY'],"{"),"}"));
	$db = new UserDb();
	$users = $db->Query("Token eq '{".$token."}'",1,0);

	if(sizeof($users)!=1){
		HttpUtils::ApiError('403', 'Invalid API key');
		die();
	}
	$user = $users[0];
	$uploader = new UploadUtils(Settings::$PackagesRoot,array("nupkg"),Settings::$MaxUploadBytes,true);
	$uploader->allowAll = true;
	$result = $uploader->Upload("package");
	if($result['hasError']) { 
		throw new Exception($result['errorCode']); }


	$fileName = basename($result["name"],".nupkg");

	$nugetReader = new NugetManager();
	$parsedNuspec = $nugetReader->LoadNuspecFromFile($result["destination"]);
	$parsedNuspec->UserId=$user->Id;
	$nuspecData = $nugetReader->SaveNuspec($result["destination"],$parsedNuspec);
		
	if($doUpLog){
			var_dump($result);
			file_put_contents("upload.log",$a."\r\nUpload completed\n", FILE_APPEND);
		}
		
	// All done!
	header('HTTP/1.1 201 Created');
}catch(Exception $ex){
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