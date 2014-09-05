<?php

require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");

require_once(__ROOT__."/inc/commons/smalltxtdb.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/db_nugetpackagesentity.php");

define('__MYTXTDB_PKG__',Path::Combine(Settings::$DataRoot,"nugetdb_pkg.txt"));
define('__MYTXTDBROWS_PKG__',
      "Version:|:Title:|:Id:|:Author:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:DownloadCount:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Published:|:Dependencies:|:".
      "PackageHash:|:PackageHashAlgorithm:|:PackageSize:|:Copyright:|:Tags:|:IsAbsoluteLatestVersion:|:".
      "IsLatestVersion:|:Listed:|:VersionDownloadCount:|:References:|:TargetFramework:|:Summary:|:IsPreRelease:|:Owners:|:UserId");
define('__MYTXTDBROWS_PKG_TYPES__',
      "string:|:string:|:string:|:object:|:string:|:string:|:string:|:number:|:".
      "boolean:|:string:|:string:|:date:|:object:|:".
      "string:|:string:|:number:|:string:|:string:|:boolean:|:".
      "boolean:|:boolean:|:number:|:array:|:string:|:string:|:boolean:|:string:|:string");      
define('__MYTXTDBROWS_PKG_EDITABLE__',
      "Tags:|:IsAbsoluteLatestVersion:|:Title:|:Title:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Copyright:|:".
      "IsLatestVersion:|:Listed:|:TargetFramework:|:Summary:|:IsPreRelease:|:Owners:|:UserId");      

function nugetDbPackageBuilder()
{
return new PackageDescriptor();
}	 
class NuGetDb
{
	public function EntityName(){ return "PackageDescriptor";}
    public function NuGetDb()
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        
    }
	
	public static function RowTypes()
	{
		return SmallTxtDb::RowTypes(__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__);
	}
	
    public function AddRow($nugetEntity,$update)
    {
        $dbInstance =  new SmallTxtDb("3.0.0.0",__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_PKG__);
        //print_r($vars);
        foreach ($vars as $column) {
			if(property_exists($nugetEntity,$column)){
				$toInsert[$column] = $nugetEntity->$column;
			}
        }
        $doAdd = true;
        for($i=0;$i<sizeof($dbInstance->rows);$i++){
            if($dbInstance->rows[$i]["PackageHash"]==$nugetEntity->PackageHash){
                if($update){
                    $dbInstance->rows[$i] = $toInsert;
                    $doAdd = false;
                 }else{
					throw new Exception("Duplicate found!");
				 }
            }
        }
        if($doAdd)$dbInstance->add_row($toInsert);
        $dbInstance->save();
        return true;
    }
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
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
    
    public function GetAllRows($limit=9999999,$skip=0,$objectSearch=null)
    {
        $this->initialize();
        $toret = array();
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
		$res =  $dbInstance->GetAll($limit,$skip,$objectSearch);
		foreach($res as $row){
			if(ends_with(strtolower($row->IconUrl),strtolower("packageDefaultIcon-50x50.png"))){
				$row->IconUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot."/content/packageDefaultIcon-50x50.png");
			}
		}
		return $res;
    }
    
    public function GetAllColumns()
    {
        return explode(":|:",__MYTXTDBROWS_PKG__);
    }
}
?>