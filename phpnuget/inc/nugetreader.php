<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/nugetentity.php");
require_once(__ROOT__."/inc/zipmanager.php");
require_once(__ROOT__."/inc/nugetdb.php");
define('__TEMPLATE_FILE__',__ROOT__."/inc/nugetTemplate.xml");

//http://net.tutsplus.com/articles/news/how-to-open-zip-files-with-php/
class NugetManager
{
    /*var $packageOnServer;
    var $nugetRootUrl;
    
    public function __construct($packageOnServer,$nugetRootUrl) 
    {
        $this->initialize($packageOnServer,$nugetRootUrl);
    }
    
    public function NugetPackageReader($packageOnServer,$nugetRootUrl)
    {
        $this->initialize($packageOnServer,$nugetRootUrl);
    }
    
    private function initialize($packageOnServer,$nugetRootUrl)
    {
        $this->packageOnServer = $packageOnServer;
        $this->nugetRootUrl = $nugetRootUrl;
    }*/
   
    public function LoadNuspecData($nupkgFile)
    {
        $zipmanager = new ZipManager($nupkgFile);
        $nuspecContent = $zipmanager->LoadFile("Microsoft.Web.Infrastructure.nuspec");
        return $nuspecContent;
    }
    
    public function LoadAllPackagesEntry()
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
        return $$toretContent;
    }
}
?>