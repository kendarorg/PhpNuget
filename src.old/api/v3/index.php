<?php
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");
require_once(__ROOT__."/inc/api_nugetv3.php");

$api = new ApiNugetBaseV3();
$api->Initialize(dirname(__FILE__));
$api->Execute();

HttpUtils::ApiError(404,"Not found");
?>