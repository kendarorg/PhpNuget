<?php
require_once(dirname(__DIR__)."/vendor/autoload.php");

use lib\OminousFactory;
use lib\rest\ui\Packages;

$properties = OminousFactory::getObject("properties");
$nugetPackages = OminousFactory::getObject("nugetPackages");
$nugetUsers = OminousFactory::getObject("nugetUsers");

$handler = new Packages($properties,$nugetUsers,$nugetPackages);
$handler->handle();

/*
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_packages.php");
require_once(__ROOT__."/inc/commons/url.php");

$id = UrlUtils::GetRequestParam("Query");
$api = new PackagesApi();
if($id!=null)
	@$api->Execute("getbyquery");
else
	@$api->Execute();*/
?>