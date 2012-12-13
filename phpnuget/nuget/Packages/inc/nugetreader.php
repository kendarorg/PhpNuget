<?php
require_once("nugetentity.php");
//http://net.tutsplus.com/articles/news/how-to-open-zip-files-with-php/
class NugetPackageReader
{
    var $packagesOnServer;
    var $phpNugetUrl;
    
    public function __construct($packagesOnServer,$phpNugetUrl) 
    {
        $this->initialize($packagesOnServer,$phpNugetUrl);
    }
    
    public function NugetPackageReader($packagesOnServer,$phpNugetUrl)
    {
        $this->initialize($packagesOnServer,$phpNugetUrl);
    }
    
    private function initialize($packagesOnServer,$phpNugetUrl)
    {
        $this->packagesOnServer = $packagesOnServer;
        $this->phpNugetUrl = $phpNugetUrl;
    }
   
    private function retrieveNugetPackages($root)
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
        $packagesList = $this->retrieveNugetPackages($this->packagesOnServer);
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