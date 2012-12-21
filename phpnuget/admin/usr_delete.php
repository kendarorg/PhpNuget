<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$nugetReader = new UserDb();
$allEntities = $nugetReader->GetAllRows();

$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);

$identifier = $_REQUEST["identifier"];
$entity = null;
for($i=0;$i<sizeof($allEntities);$i++){
    $entity = $allEntities[$i];
    if((strtolower($entity->UserId)==$identifier)){
        $nugetReader->DeleteRow($entity);
        break;
    }
}
$message = "Deleted user '".$entity->UserId."'";
if($entity==null){
    $message = "UNABLE to delete user'".$entity->UserId."'";
}
?>
<html>
    <body>
        <a href="usr_index.php">Back to user manager!</a>
        <p><?php echo $message; ?></p>
    </body>
</html>   