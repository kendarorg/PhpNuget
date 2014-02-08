<?php
if(!defined('__ROOT__')){
    define('__ROOT__', dirname(__FILE__));
}

define('__UPLOAD_DIR__', __ROOT__."/sources");
define('__PHP_NUGET_VERSION__',"2.1.0.0");
define('__MAXUPLOAD_BYTES__',10*1024*1024);
define('__API_DOWNLOAD_POSITION__',__ROOT__."/api/v2/package");
define('__PACKAGEHASH__',"SHA512"); //Or SHA256
define('__USERNAMEVAR__',"USERNAMEPHPNUGET");
define('__ISADMINVAR__',"USERNAMEPHPNUGETADMIN");
?>
