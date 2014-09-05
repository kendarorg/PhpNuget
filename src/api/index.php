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
$file = $id.".".$version.".nupkg";
$path = Path::Combine(Settings::$PackagesRoot,$file);
if(!file_exists($path)){
	HttpUtils::ApiError(404,"Not found ".$file);
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename='.basename($path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
readfile($path);
?>