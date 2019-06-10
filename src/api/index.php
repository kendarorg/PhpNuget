<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");
require_once(__ROOT__."/inc/downloadcount.php");

$id = UrlUtils::GetRequestParamOrDefault("id",null);
$apiKey = UrlUtils::GetRequestParamOrDefault("apiKey",null);

if($apiKey==null){
	if(isset($_SERVER["X-NuGet-ApiKey"])){
		$apiKey = $_SERVER["X-NuGet-ApiKey"];
	}
}
if($apiKey==null){
	if (isset($_SERVER['HTTP_X_NUGET_APIKEY'])) {
		$apiKey = $_SERVER["HTTP_X_NUGET_APIKEY"];
	}
}
if($apiKey!=null){
	$apiKey = strtoupper(trim(trim($apiKey,"{"),"}"));
}

$method = UrlUtils::RequestMethod();
$version = UrlUtils::GetRequestParamOrDefault("version",null);
if($id == null || $version == null){
	HttpUtils::ApiError(500,"Wrong data. Missing param.");
}

if(strlen($id)==0 || strlen($version)==0){
	HttpUtils::ApiError(500,"Wrong data. Empty id or version.");
}

$query = "Id eq '".$id."' and Version eq '".$version."'";
$db = new NuGetDb();
$allRows = $db->Query($query,1,0);

if(sizeof($allRows)==0){
	HttpUtils::ApiError(404,"Not found");
}

if($method=="post" || $method=="delete"){
	if($apiKey==null){
		HttpUtils::ApiError(403,"Missing Api Key");
	}
	
	$dbu = new UserDb();
	$users = $dbu->Query("Token eq '{".$apiKey."}'",1,0);
	if(sizeof($users)==0){
		HttpUtils::ApiError(404,"Not found");
	}
	
	if($allRows[0]->UserId!=$users[0]->Id && !$users[0]->Admin) HttpUtils::ApiError(403,"Missing Api Key");
	
	if(UrlUtils::ExistRequestParam("setPrerelease")){
		
		$allRows[0]->IsPreRelease=$method=="delete";
		//echo "IsPreRelease ".$allRows[0]->IsPreRelease;
	}else{
		$allRows[0]->Listed=$method=="post";
		//echo "Listed ".$allRows[0]->Listed;
	}
	
	
	$db->AddRow($allRows[0],true);
	HttpUtils::ApiError(200,"Ok");
}

$isSymbol = UrlUtils::GetRequestParamOrDefault("symbol",null)!=null;

$file = ($allRows[0]->Id.".".$allRows[0]->Version.($isSymbol?".snupkg":".nupkg"));

$path = Path::Combine(Settings::$PackagesRoot,$file);

if(!file_exists($path)){		
	//previous versions of PhpNuget did not care, so allow backwards compatibility
	$path = Path::Combine(Settings::$PackagesRoot,strtolower($file));
	if(!file_exists($path)){
		HttpUtils::ApiError(404,"Not found ".$file);
	}
}

if(!$isSymbol) {
    incrementDownload($id, $version);
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename='.basename($path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
readfile($path);