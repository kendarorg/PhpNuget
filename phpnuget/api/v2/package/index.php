<?php
define('__ROOT__',dirname(dirname( dirname(dirname(__FILE__)))));
require_once(__ROOT__."/settings.php");

$package = $_REQUEST["package"];
$destination = __UPLOAD_DIR__."/".strtolower($package).".nupkg";
//echo $destination; die();
if(file_exists($destination)){
    header("Content-Disposition: attachment; filename=\"".strtolower($package).".nupkg"."\"");
    header("Content-type: application/zip");
    readfile( $destination );
}else{
    header('HTTP/1.0 404 Not Found');
    exit("<h1>404 Not Found</h1>\nThe page that you have requested could not be found.");   
}
?>