<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 

$nugetReader = new NugetManager();
$allEntities = $nugetReader->LoadAllPackagesEntries();
usort($allEntities, "NugetManagerSortIdVersion");
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);

$entity = null;
for($i=0;$i<sizeof($allEntities);$i++){
    echo $allEntities[$i]->Identifier;
    if(strtolower($_REQUEST["identifier"])==strtolower($allEntities[$i]->Identifier)){
        
        if(strtolower($_REQUEST["version"])==strtolower($allEntities[$i]->Version)){
            $entity =$allEntities[$i];
            
        }
    }  
}
?> 
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <?php if($entity==null){ ?>
        <form action="pkg_upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file"/><br>
            <input type="submit" name="submit" value="Submit"/>
        </form>
        <?php }else{ ?>
            EDITING FORM
        <?php } ?>
        <table>
            <tr><td>Name</td><td>Version</td></tr>
        <?php 
            for($i=0;$i<sizeof($allEntities);$i++){
                $entity = $allEntities[$i];
                ?>
                <tr>
                    <td><?php echo $entity->Title;?></td><td><?php echo $entity->Version;?></td>
                    <td><a href="<?php echo $baseUtl."/api/v2/package/".strtolower($entity->Identifier)."/".$entity->Version;?>">Download</a></td>
                    <td><a href="pkg_delete.php?identifier=<?php echo strtolower($entity->Identifier);?>&version=<?php echo $entity->Version;?>">Delete</a></td>
                    <td><a href="pkg_index.php?identifier=<?php echo strtolower($entity->Identifier);?>&version=<?php echo $entity->Version;?>">Edit</a></td>
                </tr>
                <?php    
            }
        ?>
        </table>
         <a href="index.php">Back to admin!</a>
    </body>
</html> 