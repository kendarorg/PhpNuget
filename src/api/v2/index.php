<?php
require_once(dirname(__DIR__)."/vendor/autoload.php");
use lib\OminousFactory;
$version = "v2";
$request = new \lib\http\Request();
$action = trim(strtolower($request->getParam("action","")));

OminousFactory::setObject("resourcesLoaderVersion",$version);

$properties = OminousFactory::getObject("properties");
$nugetPackages = OminousFactory::getObject("nugetPackages");
$nugetUsers = OminousFactory::getObject("nugetUsers");
$resourcesLoader = OminousFactory::getObject("resourcesLoader");
$nugetQueryHandler = OminousFactory::getObject("nugetQueryHandler");
$lastQueryBuilder = OminousFactory::getObject("lastQueryBuilder");
$nugetResultParser = OminousFactory::getObject("nugetResultParser");
$nugetDownloads = OminousFactory::getObject("nugetDownloads");

$handler = null;
if($action=="findpackagesbyd"){
    $handler = new \lib\rest\FindPackagesById($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="single"){
    $handler = new \lib\rest\FindSingle($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="getupdates"){
    $handler = new \lib\rest\GetUpdates($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="metadata"){
    $handler = new \lib\rest\Metadata($version);
} else if($action=="search"){
    $handler = new \lib\rest\Search($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else{
    $handler = new \lib\rest\ApiRoot($properties,$nugetPackages,$nugetUsers,$nugetDownloads);
}

$handler->handle();
/*
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
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