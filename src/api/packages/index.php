<?php
require_once(dirname(__DIR__,2)."/config.inc");


$properties = GlobalRegistry::get("properties");
$nugetPackages = GlobalRegistry::get("nugetPackages");
$nugetUsers = GlobalRegistry::get("nugetUsers");

$handler = new Packages($properties,$nugetUsers,$nugetPackages);
$handler->handle();

/*
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/inc/api_packages.php");
require_once(__ROOT__."/inc/commons/url.php");

$id = UrlUtils::GetRequestParam("Query");
$api = new PackagesApi();
if($id!=null)
	@$api->Execute("getbyquery");
else
	@$api->Execute();*/
?>