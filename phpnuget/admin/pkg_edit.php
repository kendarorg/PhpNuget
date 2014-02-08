<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php');  
require_once(__ROOT__.'/inc/login.php'); 

$editableVars = explode(":|:",__MYTXTDBROWS_PKG_EDITABLE__);
$vars = explode(":|:",__MYTXTDBROWS_PKG__);
$types = explode(":|:",__MYTXTDBROWS_PKG__);

ManageLogin();

$nugetReader = new NugetManager();
$allEntities = $nugetReader->LoadAllPackagesEntries();
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);

$identifier = $_REQUEST["identifier"];
$version = $_REQUEST["version"];
$entity = null;

for($i=0;$i<sizeof($allEntities);$i++){
    $entity = $allEntities[$i];
    if((strtolower($entity->Identifier)==$identifier)&&(strtolower($entity->Version)==$version)){
        break;
    }
}

if($_POST["save"]=="true" && !is_null($entity)){
   
    for($i=0;$i<sizeof( $editableVars);$i++){
        $var = $editableVars[$i];
        $entity->$var = $_POST[$var];
    }
//     var_dump($entity);die();
    $nugetDb = new NuGetDb();
    $nugetDb->AddRow($entity,true);
}

$allEntities = $nugetReader->LoadAllPackagesEntries();
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



    
    

?>
<html>
    <body>
        <a href="<?php echo $baseUrl;?>">Back to root</a><br>
        <form action="pkg_edit.php?identifier=<?php echo strtolower($entity->Identifier);?>&version=<?php echo $entity->Version;?>" method="post">
            <input type="hidden" name="save" id="save" value="true"/>
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
                    }else if(is_array($entity->$var)){
                        $result = array();
                        foreach($entity->$var as $val)
                        {
                            $result[] = $val->Id." ".$val->Version;   
                        }
                        echo implode(", ",$result);
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

?>