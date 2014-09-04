<?php
require_once(__DIR__."/root.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/utils.php");
require_once(__ROOT__."/inc/commons/path.php");

if(!defined('__INSETUP__')){
    define('__INSETUP__', "__INSETUP__");
}

$applicationPath = UrlUtils::GetUrlDirectory();

if(!UrlUtils::ExistRequestParam("dosetup")){
	require_once(__ROOT__."/inc/setup/_01_accessDataAndSettings.php");
}else if( UrlUtils::GetRequestParam("dosetup","post")=="importUsers"){
	require_once(__ROOT__."/inc/setup/_02_importUsers.php");
}/*else if( UrlUtils::GetRequestParam("dosetup","post")=="importPackages"){
	require_once(__ROOT__."/inc/setup/_03_importPackages.php");
}else if( UrlUtils::GetRequestParam("dosetup","post")=="finishSetup"){
	require_once(__ROOT__."/inc/setup/_03_importPackages.php");
}*/else{
	die("Error");
}
?>