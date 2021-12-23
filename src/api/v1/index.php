<?php
require_once(dirname(__DIR__)."/vendor/autoload.php");

use lib\OminousFactory;

$version = "v1";
$request = OminousFactory::getObject("request");
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
    $handler = new \lib\rest\commons\FindPackagesById($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="single"){
    $handler = new \lib\rest\commons\FindSingle($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="metadata"){
    $handler = new \lib\rest\commons\Metadata($version);
} else if($action=="search"){
    $handler = new \lib\rest\commons\Search($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else{
    $handler = new \lib\rest\commons\ApiRoot($properties,$nugetPackages,$nugetUsers,$nugetDownloads);
}

$handler->handle();

/*
$api = new ApiNugetBaseV1();
$api->Initialize(dirname(__FILE__));
$api->Execute();

HttpUtils::ApiError(404,"Not found");*/
?>