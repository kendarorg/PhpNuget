<?php
require_once(__DIR__."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$api = new ApiNugetBaseV1();
$api->Initialize(__DIR__);
$api->Execute();

HttpUtils::ApiError(404,"Not found");
?>