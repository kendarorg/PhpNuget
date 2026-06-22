<?php
require_once(dirname(__DIR__,2)."/config.inc");


$version = "v1";
$request = GlobalRegistry::get("request");
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
} else if($action=="metadata"){
    $handler = new Metadata($version);
} else if($action=="search"){
    $handler = new Search($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else{
    $handler = new ApiRoot($properties,$nugetPackages,$nugetUsers,$nugetDownloads);
}

$handler->handle();

/*
$api = new ApiNugetBaseV1();
$api->Initialize(dirname(__FILE__));
$api->Execute();

HttpUtils::ApiError(404,"Not found");*/
?>