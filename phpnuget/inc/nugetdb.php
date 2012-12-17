<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/mytxtdb.php");
require_once(__ROOT__.'/settings.php'); 

define('__MYTXTDB__',__ROOT__."/db/nugetdb.txt");
define('__MYTXTDBROWS__',
      "Version:|:Title:|:Identifier:|:Author:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:DownloadCount:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Published:|:Dependencies:|:".
      "PackageHash:|:PackageHashAlgorithm:|:PackageSize:|:Copyright:|:Tags:|:IsAbsoluteLatestVersion:|:".
      "IsLatestVersion:|:Listed:|:VersionDownloadCount:|:References");

class NuGetDb
{
    public function __construct() 
    {
        $this->initialize();
    }
    
    public function NuGetDb()
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        
        
    }
    
    public function AddRow($nugetEntity)
    {
        $dbInstance =  new SmallTxtDb(__MYTXTDB__,__MYTXTDBROWS__);
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS__);
        //print_r($vars);
        foreach ($vars as $column) {
            $toInsert[$column] = $nugetEntity->$column;
        }
        for($i=0;$i<sizeof($dbInstance->rows);$i++){
            if($dbInstance->rows[$i]["PackageHash"]==$nugetEntity->PackageHash){
                return;   
            }
        }
        $dbInstance->add_row($toInsert);
        $dbInstance->save();
    }
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb(__MYTXTDB__,__MYTXTDBROWS__);
        $nameOfCaptain = "";
        $rowNumber = 0;
        foreach ($dbInstance->rows as $row) {
        	if ($row['PackageHash'] == $nugetEntity->PackageHash) {
        		$dbInstance->delete_row($rowNumber);
        		break;
        	}
        	$rowNumber++;
        }
        $dbInstance->save();
    }
    
    public function GetAllRows()
    {
        $this->initialize();
        $toret = array();
        $dbInstance = new SmallTxtDb(__MYTXTDB__,__MYTXTDBROWS__);
        foreach( $dbInstance->rows as $row){
            $e = new NugetEntity();
            foreach ($row as $key=> $value) {
                $e->$key = $value;
                
            }
            $toret[]=$e;
        }
        return $toret;
    }
    
    public function GetAllColumns()
    {
        return split(":|:",__MYTXTDBROWS__);
    }
}
?>