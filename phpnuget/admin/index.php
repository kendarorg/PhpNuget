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
?> 
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file"><br>
            <input type="submit" name="submit" value="Submit">
        </form>
        <table>
            <tr><td>Name</td><td>Version</td></tr>
        <?php 
            for($i=0;$i<sizeof($allEntities);$i++){
                $entity = $allEntities[$i];
                ?>
                <tr>
                    <td><?php echo $entity->Title;?></td><td><?php echo $entity->Version;?></td>
                    <td><a href="<?php echo $baseUtl."/api/v2/package/".strtolower($entity->Identifier)."/".$entity->Version;?>">Download</a></td>
                    <td><a href="delete.php?identifier=<?php echo strtolower($entity->Identifier);?>&version=<?php echo $entity->Version;?>">Delete</a></td>
                </tr>
                <?php    
            }
        ?>
        </table>
    </body>
</html> 