<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/MyTXT/MyTXT.php");
require_once(__ROOT__.'/settings.php'); 

define('__MYTXTDB__',__ROOT__."/db/nugetdb.txt");
define('__MYTXTDBROWS__',
      "Version:|:Title:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:DownloadCount:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Published:|:Dependencies:|:"
      "PackageHash:|:PackageHashAlgorithm:|:PackageSize:|:Copyright:|:Tags:|:IsAbsoluteLatestVersion:|:"
      "IsLatestVersion:|:Listed:|:VersionDownloadCount");

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
        if(!file_exists(__MYTXTDB__)){
            $fp = fopen(__MYTXTDB__, 'w+');
            fwrite($fp, __MYTXTDBROWS__);
            fclose($fp);
        }
        
    }
    
    public function AddRow($nugetEntity)
    {
        $dbInstance =  new MyTXT(__MYTXTDB__);
        $toInsert = array();
        $vars = split(":|:",__MYTXTDBROWS__);
        foreach ($vars as $column) {
            $toInsert[] = $nugetEntity->$column;
        }
        $dbInstance->add_row($toInsert);
        $dbInstance->save(__MYTXTDB__);
        $dbInstance->close();
    }
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new MyTXT(__MYTXTDB__);
        $nameOfCaptain = "";
        $rowNumber = 0;
        foreach ($dbInstance->rows as $row) {
        	if ($row['PackageHash'] == $nugetEntity->PackageHash) {
        		$dbInstance->remove_row($rowNumber);
        		break;
        	}
        	$rowNumber++;
        }
        $dbInstance->save(__MYTXTDB__);
        $dbInstance->close();
    }
    
    public function GetAllRows()
    {
        $dbInstance = new MyTXT(__MYTXTDB__);
        $rows = $dbInstance->rows;
        $dbInstance->close();
    }
    
    public function GetAllColumns()
    {
        return split(":|:",__MYTXTDBROWS__);
    }
}
?>