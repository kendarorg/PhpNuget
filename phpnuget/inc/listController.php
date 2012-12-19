<?php
define('__ROOT__',dirname(dirname( __FILE__)));
require_once(__ROOT__.'/inc/upload.php'); 
require_once(__ROOT__.'/inc/nugetreader.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 


class ListController
{
    public static function CountAll()
    {
        $nugetReader = new NugetManager();
        $allEntities = $nugetReader->LoadAllPackagesEntries();
        echo sizeof($allEntities);
    }
    public static function LoadAll()
    {
        $virtualDirectory = new VirtualDirectory();
        $baseUrl = $virtualDirectory->baseurl;
        $baseUrl = $virtualDirectory->upFromLevel($baseUrl,2);
        //header("Content-Type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";
        
        /*$req_dump = print_r($_REQUEST, TRUE);
        $fp = fopen('C:\\temp\\request.log', 'a');
        fwrite($fp, $req_dump);
        fclose($fp);*/
        
        
        $nugetReader = new NugetManager();
        $allEntities = $nugetReader->LoadAllPackagesEntries();
        
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
}

?>