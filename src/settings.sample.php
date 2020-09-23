<?php
require_once(__DIR__."/root.php");

define('__MAXUPLOAD_BYTES__',10*1024*1024*1024);
define('__PACKAGEHASH__',"SHA512"); //Or SHA256
define('__UPLOAD_DIR__', getenv("UPLOAD_DIR") ? $_ENV["UPLOAD_DIR"] : "C:\\Kendar\\Development\\PhpNuget_Evo\\PhpNuget\\src\\data\\packages");
define('__DATABASE_DIR__',getenv("DATABASE_DIR") ? $_ENV["DATABASE_DIR"] :  "C:\\Kendar\\Development\\PhpNuget_Evo\\PhpNuget\\src\\data\\db");
define('__SITE_ROOT__', "/edsa-nuget/");
define('__RESULTS_PER_PAGE__', 20);
define('__ADMINID__',"admin");
define('__ADMINPASSWORD__',"password");
define('__ADMINMAIL__',"nuget@localhost");
define('__PWDREGEX__',"/^.{8,40}$/");
define('__PWDDESC__',"Min len 8, max len 40");
define('__ALLOWGRAVATAR__',true);

//If false "Register" is disabled. Users would be only allowed to be registered by
//the admin
define('__ALLOWUSERADD__',false);

define('__ALLOWPACKAGESDELETE__',true);
define('__ALLOWPACKAGEUPDATE__', true);

// Use a http proxy for fetching external nuget
// packages. If this is not set, try to retrieve
// the value of the http_proxy environment variable
// and use that as the proxy. If both fails, don't
// use a proxy.
define('__HTTPPROXY__', '');

@define('__MYSQL_SERVER__', "127.0.0.1");
@define('__MYSQL_USER__',"root");
@define('__MYSQL_PASSWORD__',"");
@define('__MYSQL_DB__',"phpnuget");
@define('__DB_TYPE__',DBMYSQL);

// Set this to the server environemental variable username to let the web server
// handle authentication.
// examples: REMOTE_USER, PHP_AUTH_USER, SSL_CLIENT_S_DN_CN, SSL_CLIENT_SAN_OTHER_msUPN_0, REMOTE_ADDR
@define('__ENTERPRISE_AUTH_ENV__', false);

// Username displayed, this can be: UserID, Name or Email 
@define('__DISPLAY_USER__', 'UserID');

//If true users are allowed to add a package only if the firstly added it
//or if theyr user id is inside the "owners" field of the package
define('__LIMITUSERSPACKAGES__',true);

require_once(__ROOT__."/inc/internalsettings.php");
?>