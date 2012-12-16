<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/nugetentity.php");
require_once(__ROOT__."/inc/zipmanager.php");
require_once(__ROOT__."/inc/nugetdb.php");
define('__TEMPLATE_FILE__',__ROOT__."/inc/nugetTemplate.xml");

function strreplace($what,$with,$source)
{
    return str_replace($what,$with,$source);
}

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
    var $template = null;
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
        $e->Identifier = $m["id"];
        $e->Title = $m["title"];
        $e->LicenseUrl = $m["licenseurl"];
        $e->ProjectUrl = $m["projecturl"];
        $e->RequireLicenseAcceptance = $m["requirelicenseacceptance"];
        $e->Description = $m["description"];
        $e->Tags = $m["tags"];
        $e->Author = $m["authors"];
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
         
        return $e; //$this->buildNuspecEntity($e,$template);
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
        $nugetDb = new NuGetDb();
        $toret = $nugetDb->GetAllRows();
        return $toret;
    }
    
    public function BuildNuspecEntity($baseUrl,$e)
    {
        
        $t = "";
        if($this->template==null){
            $handle = fopen(__TEMPLATE_FILE__, "rb");
            $this->template = fread($handle, filesize(__TEMPLATE_FILE__));
            fclose($handle);
        }
        $t = $this->template;
        $t.="  ";
        $authors = explode(";",$e->Author);
        $author = "";
        if(sizeof($authors)>0){
            $author = "<name>".implode("</name>\n<name>",$authors)."</name>";
        }
        //print_r($e);
        $t= str_replace("\${BASEURL}",$baseUrl,$t);
        $t= str_replace("\${NUSPEC.ID}",$e->Identifier,$t);
        
        //echo $e->Id."CAZZO".$t;die();
        $t= str_replace("\${NUSPEC.IDLOWER}",strtolower($e->Identifier),$t);
        $t= str_replace("\${NUSPEC.TITLE}",$e->Title,$t);
        $t= str_replace("\${NUSPEC.VERSION}",$e->Version,$t);
        $t= str_replace("\${NUSPEC.LICENSEURL}",$e->LicenseUrl,$t);
        $t= str_replace("\${NUSPEC.PROJECTURL}",$e->ProjectUrl,$t);
        $t= str_replace("\${NUSPEC.REQUIRELICENSEACCEPTANCE}",$e->RequireLicenseAcceptance,$t);
        $t= str_replace("\${NUSPEC.DESCRIPTION}",$e->Description,$t);
        $t= str_replace("\${NUSPEC.TAGS}",$e->Tags,$t);
        $t= str_replace("\${NUSPEC.AUTHOR}",$author,$t);
        $t= str_replace("\${DB.PUBLISHED}",$e->Published,$t);
        $t= str_replace("\${DB.PACKAGESIZE}",$e->PackageSize,$t);
        $t= str_replace("\${DB.PACKAGEHASHALGORITHM}",$e->PackageHashAlgorithm,$t);
        $t= str_replace("\${DB.PACKAGEHASH}",$e->PackageHash,$t);
        
        
        $t= str_replace("\${DB.DOWNLOADCOUNT}",0,$t);
        $t= str_replace("\${DB.VERSIONDOWNLOADCOUNT}",0,$t);
        $t= str_replace("\${DB.UPDATED}",$e->Published,$t);
        //rint_r($e);die();
        return preg_replace('/<!--(.*)-->/Uis', '', $t);
    }
}
?>