<?php
require_once(dirname(__FILE__)."/root.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/utils.php");
require_once(__ROOT__."/inc/commons/path.php");

if(!defined('__INSETUP__')){
    define('__INSETUP__', "__INSETUP__");
}

$applicationPath = UrlUtils::GetUrlDirectory();

if(!UrlUtils::ExistRequestParam("dosetup")){
	require_once(__ROOT__."/inc/setup/_01_accessdataandsettings.php");
}else if( UrlUtils::GetRequestParam("dosetup","post")=="importUsers"){
	require_once(__ROOT__."/inc/setup/_02_importusers.php");
}/*else if( UrlUtils::GetRequestParam("dosetup","post")=="importPackages"){
	require_once(__ROOT__."/inc/setup/_03_importpackages.php");
}else if( UrlUtils::GetRequestParam("dosetup","post")=="finishSetup"){
	require_once(__ROOT__."/inc/setup/_03_importpackages.php");
}*/else{
	die("Error");
}
?>