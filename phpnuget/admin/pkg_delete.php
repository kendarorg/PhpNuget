<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$nugetReader = new NugetManager();
$allEntities = $nugetReader->LoadAllPackagesEntries();

$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);

$identifier = $_REQUEST["identifier"];
$version = $_REQUEST["version"];
$entity = null;
for($i=0;$i<sizeof($allEntities);$i++){
    $entity = $allEntities[$i];
    if((strtolower($entity->Identifier)==$identifier)&&(strtolower($entity->Version)==$version)){
        $nugetReader->DeleteNuspecData($entity);
        break;
    }
}
$message = "Deleted package '".$entity->Identifier."' version '".$entity->Version."'";
if($entity==null){
    $message = "UNABLE to delete package '".$entity->Identifier."' version '".$entity->Version."'";
}
?>
<html>
    <body>
        <a href="pkg_index.php">Back to files manager!</a>
        <p><?php echo $message; ?></p>
    </body>
</html>   