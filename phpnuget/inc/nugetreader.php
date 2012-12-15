<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/nugetentity.php");
require_once(__ROOT__."/inc/zipmanager.php");

//http://net.tutsplus.com/articles/news/how-to-open-zip-files-with-php/
class NugetPackageReader
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
   
    public function RetrieveNuspec($nupkgFile)
    {
        $zipmanager = new ZipManager($nupkgFile);
        $nuspecContent = $zipmanager->LoadFile("Microsoft.Web.Infrastructure.nuspec");
        return $nuspecContent;
    }
    
    private function retrieveData($root)
    {
        $toret = array();
        if ($handle = opendir($root)) {
            while (false !== ($entry = readdir($handle))) {
                $toret[] = $root."/".$entry;
            }        
            closedir($handle);
        }
        return $toret;
    }
    
    private function loadAllPackagesContent()
    {
        $toret = array();
        $packagesList = $this->retrieveNugetPackages($this->packageOnServer);
        for($i=0;$i<sizeof($packagesList);$i++){
            $packagePath =  $packagesList[$i];
            $toret[] = $this->loadPackageMetadata($packagePath);
        }
        return $toret;
    }
    
    private function loadPackageMetadata($packagePath)
    {
        $zipArchive = new ZipArchive(); 
        $zipArchive->open('theZip.zip'); 
        
        for( $i = 0; $i < $zipArchive->numFiles; $i++ ){ 
            $stat = $zipArchive->statIndex( $i ); 
            print_r( basename( $stat['name'] ) . PHP_EOL ); 
        }
    }
}
?>