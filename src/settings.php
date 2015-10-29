<?php
require_once(__DIR__."/root.php");

define('__MAXUPLOAD_BYTES__',10*1024*1024*1024);
define('__PACKAGEHASH__',"SHA512"); //Or SHA256
define('__DATA_ROOT__', "C:\\Projects\\Kendar.Framework\\Github\\endaroza\\PhpNuget.MySQL\\src");
@define('__UPLOAD_DIR__', "data".DIRECTORY_SEPARATOR."packages");
@define('__DATABASE_DIR__', "data".DIRECTORY_SEPARATOR."db");
define('__SITE_ROOT__', "/pnm/");
define('__RESULTS_PER_PAGE__', 20);
define('__ADMINID__',"admin");
define('__ADMINPASSWORD__',"password");
define('__ADMINMAIL__',"nuget@localhost");

//If false "Register" is disabled. Users would be only allowed to be registered by
//the admin
define('__ALLOWUSERADD__',false);

define('__ALLOWPACKAGESDELETE__',true);
define('__ALLOWPACKAGEUPDATE__', true);


@define('__MYSQL_SERVER__', "127.0.0.1");
@define('__MYSQL_USER__',"phpnuget");
@define('__MYSQL_PASSWORD__',"password");
@define('__MYSQL_DB__',"phpnuget");
@define('__DB_TYPE__',DBMYSQL);

//If true users are allowed to add a package only if the firstly added it
//or if theyr user id is inside the "owners" field of the package
define('__LIMITUSERSPACKAGES__',true);

require_once(__ROOT__."/inc/internalsettings.php");
?>