<?php
require_once(dirname(dirname(__FILE__))."/root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/logincontroller.php");

$specialType = strtolower(UrlUtils::GetRequestParamOrDefault("specialType",null));
if($specialType == "enterpriselogon"){
    $loginController->_enterprise_login();
}

$location = UrlUtils::CurrentUrl(Settings::$SiteRoot);
header("Location: ".$location);
die();	