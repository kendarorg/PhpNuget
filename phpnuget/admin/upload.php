<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/nugetreader.php'); 

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
    $message = "Uploaded ".$result["name"]." on ".dirname($result["destination"]);
    
    $nugetReader = new NugetPackageReader();
    $nuspecContent = $nugetReader->RetrieveNuspec($result["destination"]);
    echo $nuspecContent;die();
    //$resultList = $zipmanager->GenerateInfos();
    //print_r($resultList);die();
}

?>
<html>
    <body>
        <p><?php echo $message; ?></p>
        <a href="index.php">Back to files manager!</a>
    </body>
</html> 