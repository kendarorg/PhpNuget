<?php
define('__ROOT__',dirname(dirname( __FILE__)));
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
require_once(__ROOT__.'/inc/utils.php'); 



class ListController
{
    public static function CountAll()
    {
        $nugetReader = new NugetManager();
        $allEntities = $nugetReader->LoadAllPackagesEntries();
        echo sizeof($allEntities);
    }
    public static function LoadAll($baseUrl)
    {
        /*$file = fopen(__ROOT__."/log.txt","a+");
        fwrite($file,"Request:\n");
        foreach($_GET as $key=>$value) {
            fwrite($file,"\t". $key."=>".$value."\n");
        }
        foreach($_POST as $key=>$value) {
            fwrite($file,"\t". $key."=>".$value."\n");
        }
        } 
		fwrite($file,"\n\t".$_SERVER['REQUEST_URI']."\n");
 */
        
        header("Content-type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";
        /*$req_dump = print_r($_REQUEST, TRUE);
        $fp = fopen('C:\\temp\\request.log', 'a');
        fwrite($fp, $req_dump);
        fclose($fp);*/
        
        
        $nugetReader = new NugetManager();
        $allEntities = $nugetReader->LoadAllPackagesEntries();
        
        if(strpos($_GET["\$filter"],"IsLatestVersion")!==false){
            
            usort($allEntities,build_sorter('Version',true));
            //print_r($allEntities);die();
            $listof=array();
            for($i= (sizeof($allEntities)-1);$i >=0;$i--){
                $t = $allEntities[$i];
               
               if(!array_key_exists($t->Identifier,$allEntities)){
                $listof[$t->Identifier] = $t;
               }
            }
            $allEntities = array_values($listof);
        }
        
        $filter = str_replace("\\", "", $_GET["\$filter"]);
        
        $res= 
        //preg_match_all('@([\'"]).*[^\\\\]\1@', $filter,$out);
        preg_match_all('/(["\'])(?:\\\1|.)*?\1/', $filter,$out);
        //preg_match_all('/(?<=^|[\s,])(?:([\'"]).*?\1|[^\s,\'"]+)(?=[\s,]|$)/',$filter,$out);
        //preg_match_all("/\\'([\w]+)\\'/",$_GET["\$filter"],$out, PREG_PATTERN_ORDER);
        //print_r($out);
        //echo $res;die();
        //die();
        if($res>0 && $res!== false){
            
            $allEntities = ListController::VerifyAll($out[0],$allEntities);
        }
        
        
        
        switch($_GET["\$orderby"]){
            case("Published desc"):
                usort($allEntities, build_sorter('Published',false));
                break;
            case("Published"):
                usort($allEntities, build_sorter('Published',true));
                break;
            case("DownloadCount desc"):
                usort($allEntities, build_sorter('VersionDownloadCount',false));
                break;
            case("DownloadCount"):
                usort($allEntities, build_sorter('VersionDownloadCount',true));
                break;
            case("concat(Title,Id) desc"):
                usort($allEntities, build_sorter('Title',false));
                break;
            case("concat(Title,Id),Id"):
                usort($allEntities, build_sorter('Title',true));
                break;
            
        }
        //fwrite($file,"  SIZE: ".sizeof($allEntities));
        //fclose($file);
        if(false){
            $handle = fopen(__ROOT__.'/inc/test.xml', "rb");
            echo fread($handle, filesize(__ROOT__.'/inc/test.xml'));
            fclose($handle);
        }else{
        ?>
        <feed xml:base="<?php echo $baseUrl;?>/nuget/" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices"  xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
          <title type="text">Search</title>
          <id><?php echo $baseUrl;?>/nuget/Search</id>
          <updated>2012-12-13T19:00:52Z</updated>
          <link rel="self" title="Packages" href="Packages" />
          <?php 
            for($i=0;$i<sizeof($allEntities);$i++){
                $nuentity =  $nugetReader->BuildNuspecEntity($baseUrl,$allEntities[$i]);
                //$nuentity = str_replace(" ","&nbsp;",$nuentity);
                //$nuentity = str_replace("\n","</br>",$nuentity); 
                echo $nuentity."\n";  
            }
          
          ?>
        </feed>
     <?php
        }
    }
    
    function VerifyAll($matching,$allEntities)
    {
        for($i=0;$i<sizeof($matching);$i++){
          $matching[$i]=  str_replace("'", "", $matching[$i]);
        }
         /*print_r($matching);
                    die();*/
        $toret = array();
        for($i=0;$i<sizeof($allEntities);$i++){
           if(ListController::VerifySingle($matching,$allEntities[$i])){
              $toret[]=$allEntities[$i];
           }
        }
        
        return $toret;
    }
    
    function VerifySingle($matching,$e)
    {
        $fields = array("Identifier","Description","Tags");
        for($i=0;$i<sizeof($matching);$i++){
            for($a=0;$a<sizeof($fields);$a++){
                $val = strtolower($e->$fields[$a]);
                $exp = strtolower($matching[$i]);
               // echo $exp. " ".$val."\n";
                if(strpos($val,$exp)!==false){
                   
                     return true;  
                    }
            }
        }
        
        return false;
    }
}

?>