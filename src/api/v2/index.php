<?php
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
$api->Execute();


HttpUtils::ApiError(404,"Not found");
?>