<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php');  
require_once(__ROOT__.'/inc/login.php'); 

ManageLogin();

$nugetReader = new NugetManager();
$allEntities = $nugetReader->LoadAllPackagesEntries();
usort($allEntities, "NugetManagerSortIdVersion");
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);

$entity = null;
for($i=0;$i<sizeof($allEntities);$i++){
    $entity = $allEntities[$i];
    if((strtolower($entity->Identifier)==$identifier)&&(strtolower($entity->Version)==$version)){
        break;
    }
}

if($entity==null){
    $message = "UNABLE to find package '".$entity->Identifier."' version '".$entity->Version."'";
    die($message);
}


if($_POST["save"]=="true")
{
?>
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="pkg_edit.php" method="post">
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file"/><br>
            <input type="submit" name="submit" value="Submit"/>
        </form>
    </body>  
</html>
<?php  
}else{
    
    
    $editableVars = explode(":|:",__MYTXTDBROWS_PKG_EDITABLE__);
    $vars = explode(":|:",__MYTXTDBROWS_PKG__);
    $types = explode(":|:",__MYTXTDBROWS_PKG__);
?>
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="pkg_edit.php" method="post">
            <table>
            <?php 

            foreach($vars as $var){ ?>
            <tr><td><label for="file"><?php echo $var;?>:</label></td>
                <td><?php if(in_array($var,$editableVars)){ 
                    if(!is_bool($entity->$var)){?>
                    <input type="text" name="<?php echo $var;?>" id="<?php echo $var;?>" value="<?php echo $entity->$var; ?>"/>
                    <?php
                    }else{
                    ?>
                    <input type="checkbox" name="<?php echo $var;?>" id="<?php echo $var;?>" value="<?php echo $entity->$var; ?>"/>
                    <?php
                    }
                    ?>
                <?php 
                
                    }else{ ?>
                    <?php 
                    if(is_bool($entity->$var)){
                        echo $entity->$var?"Y":"N";   
                    }else{
                        echo $entity->$var;
                    }
                    
                    
                    ?>
                <?php } ?></td>
                </tr>
            <?php } ?>
            </table>
            <input type="submit" name="submit" value="Submit"/>
        </form>
    </body>  
</html>
<?php
}
?>