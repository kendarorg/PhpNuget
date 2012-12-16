<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/nugetentity.php");
require_once(__ROOT__."/inc/zipmanager.php");
require_once(__ROOT__."/inc/nugetdb.php");
define('__TEMPLATE_FILE__',__ROOT__."/inc/nugetTemplate.xml");

function XML2Array ( $xml , $recursive = false )
{
    if ( ! $recursive )
    {
        $array = simplexml_load_string ( $xml ) ;
    }
    else
    {
        $array = $xml ;
    }
   
    $newArray = array () ;
    $array = ( array ) $array ;
    foreach ( $array as $key => $value )
    {
        $value = ( array ) $value ;
        if ( isset ( $value [ 0 ] ) )
        {
            $newArray [ $key ] = trim ( $value [ 0 ] ) ;
        }
        else
        {
            $newArray [ $key ] = XML2Array ( $value , true ) ;
        }
    }
    return $newArray ;
}

//http://net.tutsplus.com/articles/news/how-to-open-zip-files-with-php/
class NugetManager
{
    public function LoadNuspecData($nupkgFile)
    {
        $zipmanager = new ZipManager($nupkgFile);
        $nuspecContent = $zipmanager->LoadFile("Microsoft.Web.Infrastructure.nuspec");
        $xml = XML2Array($nuspecContent);
        $e = new NugetEntity();
        $m=array();
        foreach ($xml["metadata"] as $key => $value){
            $m[strtolower ($key)]=$value;
        }
        
        /*for($i=0;$i<sizeof($ark);$i++){
            $m[strtolower ($ark[$i])]=$mt[$ark[$i]];
        }*/
        $e->Version = $m["version"];
        $e->Id = $m["id"];
        $e->Title = $m["title"];
        $e->LicenseUrl = $m["licenseurl"];
        $e->ProjectUrl = $m["projecturl"];
        $e->RequireLicenseAcceptance = $m["requirelicenseacceptance"];
        $e->Description = $m["description"];
        $e->Tags = $m["tags"];
        $e->Published = $this->iso8601();
        $e->Copyright = $m["owners"];
        $handle = fopen($nupkgFile, "rb");
        $contents = fread($handle, filesize($nupkgFile));
        fclose($handle);
        //urlsafe_b64encode
        //base64_encode
        $e->PackageHash = $this->urlsafe_b64encode(hash('sha512', $contents,true)); //true means raw, fals means in hex
        $e->PackageHashAlgorithm = "SHA512";
        $e->PackageSize = filesize($nupkgFile);
        $e->Listed = true;
         $nugetDb = new NuGetDb();
         $nugetDb->AddRow($e);
        return $nuspecContent;
    }
    
    private function urlsafe_b64encode($string) 
    { 
        $data = base64_encode($string); 
        $data = str_replace(array('+','/','='),array('-','_',''),$data); 
        return $data; 
    }
    private function iso8601($time=false) {
        if(!$time) $time=time();
        return date("Y-m-d", $time) . 'T' . date("H:i:s", $time) .'+00:00';
    }
    
    public function LoadAllPackagesEntries()
    {
        $toretContent = "";
        $handle = fopen(__TEMPLATE_FILE__, "rt");
        $templateContent = fread($handle, filesize($filename));
        fclose($handle);

        $nugetDb = new NuGetDb();
        
        $rows = $nugetDb->GetAllRows();
        $cols = $nugetDb->GetAllColumns();
        for($i=0;$i<sizeof($rows);$i++){
            $packageMetadata = $cols[$rows];
        }
        return $toretContent;
    }
}
?>