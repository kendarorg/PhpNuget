<?php
require_once(dirname(__DIR__,2)."/config.inc");


$version = "v2";
$request = new Request();
$action = trim(strtolower($request->getParam("action","")));

GlobalRegistry::setInstance("resourcesLoaderVersion",$version);

$properties = GlobalRegistry::get("properties");
$nugetPackages = GlobalRegistry::get("nugetPackages");
$nugetUsers = GlobalRegistry::get("nugetUsers");
$resourcesLoader = GlobalRegistry::get("resourcesLoader");
$nugetQueryHandler = GlobalRegistry::get("nugetQueryHandler");
$lastQueryBuilder = GlobalRegistry::get("lastQueryBuilder");
$nugetResultParser = GlobalRegistry::get("nugetResultParser");
$nugetDownloads = GlobalRegistry::get("nugetDownloads");

$handler = null;
if($action=="findpackagesbyd"){
    $handler = new FindPackagesById($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="single"){
    $handler = new FindSingle($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="getupdates"){
    $handler = new GetUpdates($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="metadata"){
    $handler = new Metadata($version);
} else if($action=="search"){
    $handler = new Search($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else{
    $handler = new ApiRoot($properties,$nugetPackages,$nugetUsers,$nugetDownloads);
}

$handler->handle();
/*
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$v2apiDebug = false;

if($v2apiDebug){
	file_put_contents("v2.log","==================================\r\n", FILE_APPEND);
	file_put_contents("v2.log","request: ".$_SERVER['REQUEST_URI']."\r\n", FILE_APPEND);
	if(sizeof($_POST)>0){
		file_put_contents("v2.log",var_export($_POST,true)."\r\n", FILE_APPEND);
	}
	if(sizeof($_GET)>0){
		file_put_contents("v2.log",var_export($_GET,true)."\r\n", FILE_APPEND);
	}
}

$api = new ApiNugetBaseV2();
$api->Initialize(dirname(__FILE__));
$filter = UrlUtils::GetRequestParam("\$filter");
if($filter!=null){
    $api->Execute("search");
}else {
    $api->Execute();
}


HttpUtils::ApiError(404,"Not found");*/
?>