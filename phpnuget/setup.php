<?php
define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 



$nugetReader = new UserDb();
$allEntities = $nugetReader->GetAllRows();
if(sizeof($allEntities)>0){
   echo "Administrator already added";die();   
}
if($_POST["UserId"]!="admin")
{
$entity=null;

$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);
?> 
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="setup.php" method="post" enctype="multipart/form-data">
            <table>
            <tr><td><label for="UserId">UserId:</label></td><td><input type="hidden" name="UserId" id="UserId" value="admin"/>admin                                         </td></tr>
            <tr><td><label for="Name">Name:</label></td><td><input type="text" name="Name" id="Name" value=""/>                                                 </td></tr>
            <tr><td><label for="Company">Company:</label></td><td><input type="text" name="Company" id="Company" value=""/>                                     </td></tr>
            <tr><td><label for="Password">Password:</label></td><td><input type="password" name="Password" id="Password" value="" />                             </td></tr>
            <tr><td><label for="PasswordConfirm">Password Confirm:</label></td><td><input type="password" name="PasswordConfirm" id="PasswordConfirm" value=""/></td></tr>
            <tr><td><label for="Packages">Packages:</label></td><td><textarea rows="4" cols="50" name="Packages" id="Packages"></textarea></td></tr>
            <tr><td><label for="Email">Email:</label></td><td><input type="text" name="Email" id="Email" value=""/>                       </td></tr>
            <tr><td><label for="Enabled">Enabled:</label></td><td><input type="hidden" name="Enabled" id="Enabled" value="true" />true  </td></tr>
            </table>
            <input type="submit" name="submit" value="Submit">
        </form>
    </body>
</html> 
<?php
}else if($_POST["UserId"]=="admin"){
 $userEntity = new UserEntity();
    $error = true;
    foreach($nugetReader->GetAllColumns() as $row)
    {
        $userEntity->$row = $_REQUEST[$row];
    }
    $userEntity->Admin="true";
    $userEntity->Enabled = "true";
    $userEntity->UserId = "admin";
    
    $confirmPasssword = $_REQUEST["PasswordConfirm"];
    $password = $_REQUEST["Password"];
    
    if($password == $confirmPasssword){
        $userEntity->Md5Password = md5($password);
        $error = false;
    }
    
    if($rror==false){
        $error = !$nugetReader->AddRow($userEntity,true);
    }
    $message = "";
    if($error==true){
        $message = "<p>Failed saving administrator '".$userEntity->UserId."'.</br>";
        $message .= "</p>";
    }else{
        $message = "<p>Saved administrator ".$userEntity->UserId."</p><p>";
        rename(__FILE__,__FILE__.".old");
    }
?>
<html>
    <body>
        <p><?php echo $message; ?></p>
    </body>
</html> 
<?php   
}
?>