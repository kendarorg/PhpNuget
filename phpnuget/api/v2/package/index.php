<?php
if(!defined('__ROOT__'))define('__ROOT__',dirname(dirname( dirname(dirname(__FILE__)))));
require_once(__ROOT__."/settings.php");
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
require_once(__ROOT__.'/inc/utils.php'); 

$package = $_REQUEST["package"];
$destination = __UPLOAD_DIR__."/".strtolower($package).".nupkg";



//echo $destination; die();
if(!file_exists($destination)){
    $nugetReader = new NugetManager();
    $allEntities = $nugetReader->LoadAllPackagesEntries();
    usort($allEntities,build_sorter('Version',true));
    for($i= (sizeof($allEntities)-1);$i >=0;$i--){
        $t = $allEntities[$i];
        if(strcasecmp($t->Identifier,$package)==0){
            $package = $t->Identifier.".".$t->Version;
            $destination = __UPLOAD_DIR__."/".strtolower($package).".nupkg";
            break;
        }
    }   
    
}

if(file_exists($destination)){
    header("Content-Disposition: attachment; filename=\"".strtolower($package).".nupkg"."\"");
    header("Content-type: application/zip");
    readfile( $destination );
}else{
    header('HTTP/1.0 404 Not Found');
    exit("<h1>404 Not Found</h1>\nThe page that you have requested could not be found.");   
}
?>