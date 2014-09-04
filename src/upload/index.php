<?php

require_once(__DIR__."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/db_users.php");
require_once(__ROOT__."/inc/nugetreader.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/uploadutils.php");

require_once(__ROOT__."/inc/commons/objectsearch.php");

$temp_file = tempnam(sys_get_temp_dir(), 'Tux');

try{
	
	if (empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
		HttpUtils::ApiError('403', 'Invalid API key');
		die();
	}
	
	$token = strtoupper(trim(trim($_SERVER['HTTP_X_NUGET_APIKEY'],"{"),"}"));
	$db = new UserDb();

	$os = new ObjectSearch();
	$os->Parse("Token eq '{".$token."}'",$db->GetAllColumns());

	$users = $db->GetAllRows(1,0,$os);

	if(sizeof($users)!=1){
		HttpUtils::ApiError('403', 'Invalid API key');
		die();
	}
	$user = $users[0];
	$uploader = new UploadUtils(Settings::$PackagesRoot,array("nupkg"),Settings::$MaxUploadBytes,true);
	$uploader->allowAll = true;
	$result = $uploader->Upload("package");



	$fileName = basename($result["name"],".nupkg");

	$nugetReader = new NugetManager();
	$parsedNuspec = $nugetReader->LoadNuspecFromFile($result["destination"]);

	$parsedNuspec->UserId=$user->Id;
	$nuspecData = $nugetReader->SaveNuspec($result["destination"],$parsedNuspec);
		
	// All done!
	header('HTTP/1.1 201 Created');
}catch(Exception $ex){
	HttpUtils::ApiError('500', $ex->getMessage());
	unlink($temp_file);
}
?>