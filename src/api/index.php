<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$id = UrlUtils::GetRequestParamOrDefault("id",null);
$version = UrlUtils::GetRequestParamOrDefault("version",null);
if($id == null || $version == null){
	HttpUtils::ApiError(500,"Wrong data. Missing param.");
}

if(strlen($id)==0 || strlen($version)==0){
	HttpUtils::ApiError(500,"Wrong data. Empty id or version.");
}

$query = "Id eq '".$id."' and Version eq '".$version."'";
$db = new NuGetDb();
$os = new PhpNugetObjectSearch();
$os->Parse($query,$db->GetAllColumns());
$allRows = $db->GetAllRows(1,0,$os);

if(sizeof($allRows)==0){
	HttpUtils::ApiError(404,"Not found");
}


$file = ($allRows[0]->Id.".".$allRows[0]->Version.".nupkg");

$path = Path::Combine(Settings::$PackagesRoot,$file);

if(!file_exists($path)){		
	//previous versions of PhpNuget did not care, so allow backwards compatibility
	$path = Path::Combine(Settings::$PackagesRoot,strtolower($file));
	if(!file_exists($path)){
		HttpUtils::ApiError(404,"Not found ".$file);
	}
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename='.basename($path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
readfile($path);