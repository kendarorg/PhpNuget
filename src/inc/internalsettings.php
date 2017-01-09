<?php

require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/inc/commons/path.php");

define('__RW_ADMIN_R_ALL__',0644);

class Settings
{
	public static $PackageHash = __PACKAGEHASH__;
	public static $MaxUploadBytes = __MAXUPLOAD_BYTES__;
	public static $Version = "3.0.12.11";
	public static $ResultsPerPage = __RESULTS_PER_PAGE__;
	public static $SiteRoot = __SITE_ROOT__;
	public static $DataRoot = "";
	public static $PackagesRoot = "";
	public static $SitePath = "";
	public static $AdminId = "";
	public static $AdminPassword = "";
	public static $AdminEmail = "";
	public static $AllowUserAdd = false;
	public static $LimitUsersPackages = false;
}

Settings::$SitePath = Path::CleanUp(__ROOT__);
Settings::$DataRoot = __DATABASE_DIR__;
Settings::$PackagesRoot = __UPLOAD_DIR__;
Settings::$PackageHash = __PACKAGEHASH__;
Settings::$MaxUploadBytes = __MAXUPLOAD_BYTES__;
$sr = trim(__SITE_ROOT__,"/\\");
if($sr!=""){
	Settings::$SiteRoot = "/".$sr."/";
}else{
	Settings::$SiteRoot = "/";
}
Settings::$AllowUserAdd = __ALLOWUSERADD__;
Settings::$AdminId = __ADMINID__;
Settings::$AdminPassword = __ADMINPASSWORD__;
Settings::$AdminEmail = __ADMINMAIL__;
Settings::$LimitUsersPackages = __LIMITUSERSPACKAGES__;


if(!is_dir(Settings::$DataRoot))
	mkdir(Settings::$DataRoot,__RW_ADMIN_R_ALL__);
if(!is_dir(Settings::$PackagesRoot))	
	mkdir(Settings::$PackagesRoot,__RW_ADMIN_R_ALL__);

require_once(__ROOT__."/inc/logincontroller.php");


$doUpLog = true;
$doLogBin = true;

function uplog($who,$data)
{
	global $doUpLog;
	if(!$doUpLog)return;
	file_put_contents($who.".log",$data."\r\n", FILE_APPEND);
}
function uplogh($who,$str,$data)
{
	global $doUpLog;
	if(!$doUpLog)return;
	if(sizeof($data)>0){
		uplog($who,$str);
		uplog($who,var_export($data,true));
	}else{
		uplog($who,$str."-EMPTY");
	}
}
function uplogv($who,$str,$data)
{
	global $doUpLog;
	if(!$doUpLog)return;
	if(sizeof($data)>0){
		uplog($who,$str);
		uplog($who,var_dump($data,true));
	}else{
		uplog($who,$str."-EMPTY");
	}
}
function uplogb($who,$str,$data)
{
	global $doUpLog;
	if(!$doUpLog)return;
	global $doLogBin;
	if(!$doLogBin)return;
	if(sizeof($data)>0){
		uplog($who,$str);
		uplog($who,$data);
	}else{
		uplog($who,$str."-EMPTY");
	}
}
?>