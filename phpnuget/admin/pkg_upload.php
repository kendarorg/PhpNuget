<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;

$zipmanager = null;

$uploader = new Uploader(__UPLOAD_DIR__);
$result = $uploader->upload(array("nupkg"),__MAXUPLOAD_BYTES__);
$message = "";
if($result["hasError"]==true){
    $message = "<p>Failed uploading '".$result["name"]."'.</br>";
    $message .= "Error is: ".$result["errorMessage"];
    if($result["errorCode"]!=null){
        $message .= "</br>Error code is:".$result["errorCode"]."."; 
    }
    $message .= "</p>";
}else{
    $message = "Uploaded ".$result["name"]." on ".dirname($result["destination"])."</p><p>";
    
    $nugetReader = new NugetManager();
    $nuentity =  htmlentities($nugetReader->BuildNuspecEntity($baseUrl,$nugetReader->LoadNuspecData($result["destination"])));
    $nuentity = str_replace(" ","&nbsp;",$nuentity);
    $nuentity = str_replace("\n","</br>",$nuentity);
    $message .= $nuentity;
    //$message = echo $nuspecContent;die();
    //$resultList = $zipmanager->GenerateInfos();
   //print_r($nuspecContent);die();
}

?>
<html>
    <body>
        <a href="pkg_index.php">Back to files manager!</a>
        <p><?php echo $message; ?></p>
    </body>
</html> 