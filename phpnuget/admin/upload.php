<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/upload.php'); 
$uploader = new Uploader(__UPLOAD_DIR__);
$result = $uploader->upload(array("nupkg","pdf"),20000);
$message = "";
if($result["hasError"]==true){
    $message = "<p>Failed uploading '".$result["name"]."'.</br>";
    $message .= "Error is: ".$result["errorMessage"];
    if($result["errorCode"]!=null){
        $message .= "</br>Error code is:".$result["errorCode"]."."; 
    }
    $message .= "</p>";
}else{
    $message = "Uploaded ".$result["name"]." on ".dirname($toret["destination"]);
}

?>
<html>
    <body>
        <p><?php echo $message; ?></p>
        <a href="index.php">Back to files manager!</a>
    </body>
</html> 