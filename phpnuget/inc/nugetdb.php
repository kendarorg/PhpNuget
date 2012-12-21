<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/mytxtdb.php");
require_once(__ROOT__.'/settings.php'); 

define('__MYTXTDB_PKG__',__ROOT__."/db/nugetdb_pkg.txt");
define('__MYTXTDBROWS_PKG__',
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
    
    public function AddRow($nugetEntity,$update)
    {
        $dbInstance =  new SmallTxtDb(__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__);
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_PKG__);
        //print_r($vars);
        foreach ($vars as $column) {
            $toInsert[$column] = $nugetEntity->$column;
        }
        $doAdd = true;
        for($i=0;$i<sizeof($dbInstance->rows);$i++){
            if($dbInstance->rows[$i]["PackageHash"]==$nugetEntity->PackageHash){
                if($update){
                    $dbInstance->rows[$i] = $toInsert[$column];
                    $doAdd = false;
                 }
            }
        }
    
        if($doAdd)$dbInstance->add_row($toInsert);
        $dbInstance->save();
        return true;
    }
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb(__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__);
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
        $dbInstance = new SmallTxtDb(__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__);
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
        return split(":|:",__MYTXTDBROWS_PKG__);
    }
}
?>