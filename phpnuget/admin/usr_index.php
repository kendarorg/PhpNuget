<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
require_once(__ROOT__.'/inc/utils.php');  
require_once(__ROOT__.'/inc/login.php'); 

ManageLogin();

$nugetReader = new UserDb();
$allEntities = $nugetReader->GetAllRows();

if(!IsAdmin()){
   $newEntities = array();
   for($i=0;$i<sizeof($allEntities);$i++){
        if(strtolower( UserName())==strtolower($allEntities[$i]->UserId)){
            $newEntities[] =$allEntities[$i];
        }  
    }
    $allEntities = $newEntities;
}

usort($allEntities,build_sorter('UserId',true));
$entity = null;
for($i=0;$i<sizeof($allEntities);$i++){
    if($_REQUEST["identifier"]==$allEntities[$i]->UserId){
        $entity =$allEntities[$i];
    }  
}



$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);
?> 
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="usr_upload.php" method="post" enctype="multipart/form-data">
            <table>
            <tr><td><label for="UserId">UserId:</label></td><td><input type="text" name="UserId" id="UserId" value="<?php echo $entity==null?"":$entity->UserId;?>"/>                                         </td></tr>
            <tr><td><label for="Name">Name:</label></td><td><input type="text" name="Name" id="Name" value="<?php echo $entity==null?"":$entity->Name;?>"/>                                                 </td></tr>
            <tr><td><label for="Company">Company:</label></td><td><input type="text" name="Company" id="Company" value="<?php echo $entity==null?"":$entity->Company;?>"/>                                     </td></tr>
            <tr><td><label for="Password">Password:</label></td><td><input type="password" name="Password" id="Password" value="" />                             </td></tr>
            <tr><td><label for="PasswordConfirm">Password Confirm:</label></td><td><input type="password" name="PasswordConfirm" id="PasswordConfirm" value=""/></td></tr>
            <tr><td><label for="Packages">Packages:</label></td><td><textarea rows="4" cols="50" name="Packages" id="Packages"><?php echo $entity==null?"":$entity->Packages;?></textarea></td></tr>
            <tr><td><label for="Email">Email:</label></td><td><input type="text" name="Email" id="Email" value="<?php echo $entity==null?"":$entity->Email;?>"/>                       </td></tr>
            <tr><td><label for="Enabled">Enabled:</label></td><td><input type="text" name="Enabled" id="Enabled" value="true" value="<?php echo $entity==null?"":$entity->Enabled;?>"/>  </td></tr>
            </table>
            <input type="submit" name="submit" value="Submit">
        </form>
        <table>
            <tr><td>Name</td><td>UserId</td><td>Token</td><td></td><td></td></tr>
        <?php 
            for($i=0;$i<sizeof($allEntities);$i++){
                $entity = $allEntities[$i];
                ?>
                <tr>
                    <td><?php echo $entity->Name;?></td><td><?php echo $entity->UserId;?></td><td><?php echo $entity->Token;?></td>
                    <td><a href="usr_delete.php?identifier=<?php echo strtolower($entity->UserId);?>">Delete</a></td>
                    <td><a href="usr_index.php?identifier=<?php echo strtolower($entity->UserId);?>">Edit</a></td>
                </tr>
                <?php    
            }
        ?>
        </table>
    </body>
</html> 