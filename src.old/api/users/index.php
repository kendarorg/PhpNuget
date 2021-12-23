<?php
require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/logincontroller.php");

if(!$loginController->Admin){
	$uid = UrlUtils::GetRequestParam("UserId");
	if($uid!=$loginController->UserId){
		HttpUtils::ApiError(500,"Unauthorized");
	}
}
$api = new UsersApi();
$id = UrlUtils::GetRequestParamOrDefault("UserId","get");
$api->Execute();
?>