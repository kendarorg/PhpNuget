<?php

use lib\rest\ui\Packages;

$handler = new Packages();
$handler->handle();

require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_packages.php");
require_once(__ROOT__."/inc/commons/url.php");

$id = UrlUtils::GetRequestParam("Query");
$api = new PackagesApi();
if($id!=null)
	@$api->Execute("getbyquery");
else
	@$api->Execute();
?>