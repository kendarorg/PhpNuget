<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$nugetReader = new UserDb();
$allEntities = $nugetReader->GetAllRows();
usort($allEntities, "UserDbSortUserId");
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);
?> 
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="usr_upload.php" method="post" enctype="multipart/form-data">
            <label for="UserId">UserId:</label><input type="text" name="UserId" id="UserId"/><br>
            <label for="Name">Name:</label><input type="text" name="Name" id="Name"/><br>
            <label for="Company">Company:</label><input type="text" name="Company" id="Company"/><br>
            <label for="Password">Password:</label><input type="password" name="Password" id="Password"/><br>
            <label for="PasswordConfirm">Password Confirm:</label><input type="password" name="PasswordConfirm" id="PasswordConfirm"/><br>
            <label for="Packages">Packages:</label><input type="text" name="Packages" id="Packages"/><br>
            <label for="Email">Email:</label><input type="text" name="Email" id="Email"/><br>
        <label for="Enabled">Enabled:</label><input type="text" name="Enabled" id="Enabled" value="true"/><br>
            <input type="submit" name="submit" value="Submit">
        </form>
        <table>
            <tr><td>Name</td><td>UserId</td><td>Token</td></tr>
        <?php 
            for($i=0;$i<sizeof($allEntities);$i++){
                $entity = $allEntities[$i];
                ?>
                <tr>
                    <td><?php echo $entity->Name;?></td><td><?php echo $entity->UserId;?></td><td><?php echo $entity->Token;?></td>
                    <td><a href="usr_delete.php?identifier=<?php echo strtolower($entity->UserId);?>">Delete</a></td>
                </tr>
                <?php    
            }
        ?>
        </table>
    </body>
</html> 