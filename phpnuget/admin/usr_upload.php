<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$nugetReader = new UserDb();
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;

$userEntity = new UserEntity();
$error = true;
foreach($nugetReader->GetAllColumns() as $row)
{
//    echo $row."</br>";
    $userEntity->$row = $_REQUEST[$row];
}

$confirmPasssword = $_REQUEST["PasswordConfirm"];
$password = $_REQUEST["Password"];

if($password == $confirmPasssword){
    $userEntity->Md5Password = md5($password);
    $error = false;
}
$userEntity->Enabled = "false";
if(strtolower($_REQUEST["Enabled"]) =="true" || strtolower($_REQUEST["Enabled"]) =="yes"){
    $userEntity->Enabled = "true";
}
if($rror==false){
    $error = !$nugetReader->AddRow($userEntity,true);
}
$message = "";
if($error==true){
    $message = "<p>Failed uploading '".$userEntity->UserId."'.</br>";
    $message .= "</p>";
}else{
    $message = "<p>Uploaded ".$userEntity->UserId."</p><p>";
}

?>
<html>
    <body>
        <a href="usr_index.php">Back to users manager!</a>
        <p><?php echo $message; ?></p>
    </body>
</html> 