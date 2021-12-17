<?php

namespace api\v1;

/*require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");*/
$version = "v1";
$action = null;
if(isset($_REQUEST["action"])){
    $action = trim(strtolower($_REQUEST["action"]));
}

$fileName = dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR."properties.json";
$properties = new \lib\utils\Properties($fileName);
$dbStorage = new \lib\db\file\FileDbStorage($properties);
$nugetPackages = new \lib\nuget\NugetPackages($dbStorage);
$resourcesLoader = new \lib\rest\utils\ResourcesLoader($version);
$nugetQueryHandler = new \lib\rest\utils\NugetQueryHandler($nugetPackages);
$lastQueryBuilder = new \lib\rest\utils\LastQueryBuilder();
$nugetResultParser = new \lib\rest\utils\NugetResultParser($resourcesLoader,$lastQueryBuilder);
$nugetUsers = new \lib\nuget\NugetUsers($dbStorage);
$nugetDownloads = new \lib\nuget\NugetDownloads();

$handler = null;
if($action=="findpackagesbyd"){
    $handler = new \lib\rest\FindPackagesById($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="single"){
    $handler = new \lib\rest\FindSingle($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else if($action=="metadata"){
    $handler = new \lib\rest\Metadata($version);
} else if($action=="search"){
    $handler = new \lib\rest\Search($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser);
} else{
    $handler = new \lib\rest\ApiRoot($properties,$nugetPackages,$nugetUsers,$nugetDownloads);
}

$handler->handle();

/*
$api = new ApiNugetBaseV1();
$api->Initialize(dirname(__FILE__));
$api->Execute();

HttpUtils::ApiError(404,"Not found");*/
?>